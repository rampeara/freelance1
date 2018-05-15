<?php

namespace LeavesOvertimeBundle\EventListener;

use Application\Sonata\UserBundle\Entity\User;
use LeavesOvertimeBundle\Common\Utility;
use LeavesOvertimeBundle\Entity\BalanceLog;
use LeavesOvertimeBundle\Entity\Leaves;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;

class LeavesSubscriber implements EventSubscriber
{
    private $context;
    private $utility;
    private $container;
    
    public function __construct($securityContext, $container)
    {
        $this->context= $securityContext;
        $this->utility = new Utility($container);
        $this->container = $container;
    }
    
    /**
     * @return $this|\Application\Sonata\UserBundle\Entity\User
     */
    private function getUser()
    {
        if ($this->context->getToken() != null) {
            return $this->context->getToken()->getUser();
        }
        return null;
    }
    
    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
            'prePersist',
        );
    }
    
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if ($entity instanceof Leaves) {
            if (empty($entity->getType())) {
                $entity->setType($entity::TYPE_LOCAL_LEAVE);
            }
            if (empty($entity->getUser())) {
                $entity->setUser($this->getUser());
            }
            if (empty($entity->getStatus())) {
                $entity->setStatus($entity::STATUS_REQUESTED);
            }
        }
    }
    
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->processLeaves($eventArgs);
    }
    
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $this->processLeaves($eventArgs);
    }
    
    /**
     * @param \Doctrine\Common\Persistence\Event\LifecycleEventArgs $eventArgs
     */
    public function processLeaves(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if ($entity instanceof Leaves) {
            $leaves = $entity;
            $absenceLeaveTypes = [
                $leaves::TYPE_ABSENCE_FROM_WORK,
                $leaves::TYPE_INJURY_LEAVE_WITHOUT_PAY,
                $leaves::TYPE_LEAVE_WITHOUT_PAY,
                $leaves::TYPE_MATERNITY_LEAVE_WITHOUT_PAY,
                $leaves::TYPE_PATERNITY_LEAVE_WITHOUT_PAY,
                $leaves::TYPE_WEDDING_LEAVE_WITHOUT_PAY,
            ];
            $objectManager = $eventArgs->getObjectManager();
            $leaveType = $leaves->getType();

            if ($leaveType == $leaves::TYPE_SICK_LEAVE || $leaveType == $leaves::TYPE_LOCAL_LEAVE) {
                $this->updateLeaveBalance($leaves, $objectManager);
            }
            elseif (in_array($leaveType, $absenceLeaveTypes) && $leaves->getStatus() == $leaves::STATUS_APPROVED) {
                $this->updateUserLastAbsenceDate($leaves, $objectManager);
            }

            $this->sendEmail($leaves, $objectManager);
        }
    }
    
    /**
     * @param Leaves $leaves
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    private function updateLeaveBalance($leaves, $objectManager)
    {
        $leaveStatus = $leaves->getStatus();
        if (!($leaveStatus == $leaves::STATUS_APPROVED || $leaveStatus == $leaves::STATUS_CANCELLED)) {
            return;
        }
    
        $user = $leaves->getUser();
        $currentUser = $this->getUser()->getUsername();
        $isSickLeave = $leaves->getType() == $leaves::TYPE_SICK_LEAVE;
        list($previousBalance, $newBalance, $carryForwardAmount) = $this->updateUserBalance($leaves, $user, $isSickLeave, $leaveStatus, $objectManager);
    
        $notCarryForward = $carryForwardAmount == null;
        $leaveTypeText = $isSickLeave ? 'Sick' : ($notCarryForward ? 'Local' : 'Local + Carry Forward');
        $carryForwardAmountText = $notCarryForward ? '' : sprintf(', carry forward amount %s', $carryForwardAmount);
        $balanceLog = new BalanceLog();
        $balanceLog
            ->setDescription(sprintf($balanceLog::TYPE_APPLIED_LEAVE_DESC, $leaveStatus, $leaveTypeText, $previousBalance, $newBalance, $carryForwardAmountText))
            ->setUser($user)
            ->setCreatedBy($currentUser)
            ->setLeave($leaves)
            ->setCarryForwardAmount($carryForwardAmount)
        ;
        
        $objectManager->persist($balanceLog);
        $objectManager->persist($user);
        $objectManager->flush();
    }
    
    /**
     * @param \Application\Sonata\UserBundle\Entity\User $leaveApplicant
     * @param boolean $allSupervisors
     *
     * @return array
     */
    private function getSupervisorsEmails($leaveApplicant, $allSupervisors = false)
    {
        $emailTo = [];
        if ($leaveApplicant) {
            $supervisors = $leaveApplicant->getSupervisorsLevel1();
            if ($supervisors) {
                foreach ($supervisors as $supervisor) {
                    $emailTo[] = $supervisor->getEmail();
                }
            }
            if ($allSupervisors) {
                $supervisors = $leaveApplicant->getSupervisorsLevel2();
                if ($supervisors) {
                    foreach ($supervisors as $supervisor) {
                        $emailTo[] = $supervisor->getEmail();
                    }
                }
            }
        }
        return $emailTo;
    }
    
    /**
     * @param Leaves $leaves
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    private function sendEmail($leaves, $objectManager)
    {
        $templateName = $leaves->getStatus();
        $emailTo = [];
        $cc = [];
        $leaveApplicant = $leaves->getUser();
        // mail to supervisors
        if ($templateName == $leaves::STATUS_REQUESTED) { //|| $templateName == $leaves::STATUS_WITHDRAWN
            $emailTo = $this->getSupervisorsEmails($leaveApplicant, TRUE);
            $cc = $leaveApplicant->getEmail();
        }
        // mail to leave applicant, cc all supervisors
        else {
            $emailTo[] = $leaveApplicant->getEmail();
            $cc = $this->getSupervisorsEmails($leaveApplicant, TRUE);
        }
        
        if ($emailTo) {
            $template = $objectManager->getRepository('LeavesOvertimeBundle:EmailTemplate')
                ->findOneBy(['name' => $templateName]);
            if ($template) {
                $templateContent = $template->getContent();
                $templateContent = $this->replaceTemplateVariables($leaves, $templateContent);
                
                $emailOptions = [
                    'subject' => sprintf('%s %s: %s', $leaves->getType(), $templateName, $leaves->getUser()->getFullname()),
                    'from' => $this->container->getParameter('mailer_from_email'),
                    'to' => $emailTo,
                    'body' => $templateContent,
                ];
                if ($cc) {
                    $emailOptions['cc'] = $cc;
                }
                
                $this->utility->sendSwiftMail($emailOptions);
            }
        }
    }
    
    /**
     * @param Leaves $leaves
     * @param \Application\Sonata\UserBundle\Entity\User $user
     * @param bool $isSickLeave
     * @param string $leaveStatus
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     *
     * @return array
     */
    private function updateUserBalance($leaves, &$user, $isSickLeave, $leaveStatus, $objectManager)
    {
        $isApprovedStatus = $leaveStatus == $leaves::STATUS_APPROVED;
        $leaveDuration = $leaves->getDuration();
        
        if ($isSickLeave) {
            $previousBalance = $user->getSickBalance();
            $newBalance = $isApprovedStatus ? $previousBalance - $leaveDuration : $previousBalance + $leaveDuration;
            $user->setSickBalance($newBalance);
            return [$previousBalance, $newBalance, null];
        }

        if ($isApprovedStatus) {
            // if no carry forward to work with, operate with local balance directly
            $carryForwardLocalBalance = $user->getCarryForwardLocalBalance();
            if ($carryForwardLocalBalance == 0) {
                $previousBalance = $user->getLocalBalance();
                $newBalance = $previousBalance - $leaveDuration;
                $user->setLocalBalance($newBalance);
                return [$previousBalance, $newBalance, null];
            }
            
            // reduce from carry forward local balance first then local balance
            $netCarryForwardLocalBalance = $carryForwardLocalBalance - $leaveDuration;
            $previousBalance = $user->getTotalLocalBalance();
            // after deduction cf balance is positive, remove directly
            if ($netCarryForwardLocalBalance >= 0) {
                $user->setCarryForwardLocalBalance($netCarryForwardLocalBalance);
                return [$previousBalance, $user->getTotalLocalBalance(), $leaveDuration];
            }
            // else remove negative difference from local, set cf to 0
            else {
                $reduceFromLocalBalanceAmount = abs($netCarryForwardLocalBalance);
                $user->setCarryForwardLocalBalance(0);
                $user->setLocalBalance($user->getLocalBalance() - $reduceFromLocalBalanceAmount);
                return [$previousBalance, $user->getTotalLocalBalance(), $carryForwardLocalBalance];
            }
        }
        else {
            // find associated approved request
            $previousBalance = $user->getTotalLocalBalance();
            $approvedLeaveBalanceLog = $objectManager->getRepository('LeavesOvertimeBundle:BalanceLog')->findOneBy(['leave' => $leaves->getId()], ['createdBy' => 'ASC']);
            $carryForwardAmount = $approvedLeaveBalanceLog->getCarryForwardAmount();
            if ($carryForwardAmount) {
                $user->setLocalBalance($user->getLocalBalance() + ($leaves->getDuration() - $carryForwardAmount));
                $user->setCarryForwardLocalBalance($user->getCarryForwardLocalBalance() + $carryForwardAmount);
                return [$previousBalance, $user->getTotalLocalBalance(), $carryForwardAmount];
            }
            else {
                $user->setLocalBalance($user->getLocalBalance() + $leaves->getDuration());
                return [$previousBalance, $user->getTotalLocalBalance(), null];
            }
        }
    }
    
    /**
     * @param Leaves $leaves
     * @param $template
     *
     * @return string
     */
    private function replaceTemplateVariables($leaves, $template)
    {
        $dateFormat = 'd/m/Y';
        $searchFor = [
            '[applicant_full_name]',
            '[leave_type]',
            '[leave_start_date]',
            '[leave_end_date]',
            '[leave_duration]',
            '[leave_created_at]',
            '[signature_name]',
        ];
        $replaceWith = [
            $leaves->getUser()->getFullname(),
            $leaves->getType(),
            $leaves->getStartDate()->format($dateFormat),
            $leaves->getEndDate()->format($dateFormat),
            $leaves->getType() == $leaves::TYPE_SICK_LEAVE ? $leaves->getHours() . ' hour(s)' : $leaves->getDuration() . ' day(s)',
            $leaves->getCreatedAt()->format($dateFormat . ' H:i:s'),
            $this->container->getParameter('application_leaves_email_signature')
        ];
        return str_replace($searchFor, $replaceWith, $template);
    }

    /**
     * @param $leaves
     * @param $objectManager
     */
    private function updateUserLastAbsenceDate($leaves, $objectManager): void
    {
        $user = $leaves->getUser();
        $user->setLastAbsenceDate($leaves->getEndDate());
        $objectManager->persist($user);
        $objectManager->flush();
    }
}