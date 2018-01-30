<?php

namespace LeavesOvertimeBundle\EventListener;

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
            $entityManager = $eventArgs->getObjectManager();
            $this->updateLeaveBalance($entity, $entityManager);
            $this->sendEmail($entity, $entityManager);
        }
    }
    
    /**
     * @param Leaves $leaves
     * @param $entityManager
     */
    private function updateLeaveBalance($leaves, $entityManager)
    {
        if ($leaves->getStatus() == $leaves::STATUS_APPROVED || $leaves->getStatus() == $leaves::STATUS_CANCELLED) {
            $user = $leaves->getUser();
            $currentUser = $this->getUser()->getUsername();
            list($previousBalance, $newBalance) = $this->updateUserBalance($leaves, $user);

            $entityManager->persist(new BalanceLog($previousBalance, $newBalance, $user, $currentUser, null, $leaves));
            $entityManager->persist($user);
            $entityManager->flush();
        }
    }
    
    /**
     * @param \Application\Sonata\UserBundle\Entity\User $leaveApplicant
     * @param boolean $allSupervisors
     *
     * @return array
     */
    private function getSupervisorsEmails($leaveApplicant, $allSupervisors = false): array
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
     * @param $entityManager
     */
    private function sendEmail($leaves, $entityManager)
    {
        $templateName = $leaves->getStatus();
        $emailTo = [];
        $cc = [];
        $leaveApplicant = $leaves->getUser();
        // mail to supervisors
        if ($templateName == $leaves::STATUS_REQUESTED) { //|| $templateName == $leaves::STATUS_WITHDRAWN
            $emailTo = $this->getSupervisorsEmails($leaveApplicant, TRUE);
        }
        // mail to leave applicant, cc all supervisors
        else {
            $emailTo[] = $leaveApplicant->getEmail();
            $cc = $this->getSupervisorsEmails($leaveApplicant, TRUE);
        }
        
        if ($emailTo) {
            $template = $entityManager->getRepository('LeavesOvertimeBundle:EmailTemplate')
                ->findOneBy(['name' => $templateName]);
            if ($template) {
                $templateContent = $template->getContent();
                $templateContent = $this->replaceTemplateVariables($leaves, $templateContent);
                
                $emailOptions = [
                    'subject' => sprintf('%s %s: %s', $leaves->getType(), $templateName, $leaves->getUser()->getFullname()),
                    'from' => $this->container->getParameter('from_email'),
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
     * @param $leaves
     * @param $user
     *
     * @return array
     */
    private function updateUserBalance($leaves, &$user): array
    {
        $isSickLeave = $leaves->getType() == $leaves::TYPE_SICK_LEAVE;
        $isApprovedStatus = $leaves->getStatus() == $leaves::STATUS_APPROVED;
        $previousBalance = $isSickLeave ? $user->getSickBalance() : $user->getLocalBalance();
        $newBalance = $isApprovedStatus ? $previousBalance - $leaves->getDuration() : $previousBalance + $leaves->getDuration();
        if ($isSickLeave) {
            $user->setSickBalance($newBalance);
        }
        else {
            $user->setLocalBalance($newBalance);
        }
        return [$previousBalance, $newBalance];
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
            $this->container->getParameter('leaves_email_signature_name')
        ];
        return str_replace($searchFor, $replaceWith, $template);
    }
}