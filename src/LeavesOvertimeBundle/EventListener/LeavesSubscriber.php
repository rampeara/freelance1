<?php

namespace LeavesOvertimeBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
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
            'preUpdate',
        );
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
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
    
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getObject() instanceof Leaves) {
            /** @var Leaves $entity */
            $entity = $eventArgs->getObject();
            if (
                $eventArgs->hasChangedField('status')
                && $eventArgs->getNewValue('status') == $entity::STATUS_CANCELLED
                && $eventArgs->getOldValue('status') != $entity::STATUS_APPROVED
            ) {
                throw new \Exception(sprintf('Only leaves with status "%s" can be cancelled.', $entity::STATUS_APPROVED));
            }
        }
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->processLeaves($args);
    }
    
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->processLeaves($args);
    }
    
    /**
     * @param \Doctrine\Common\Persistence\Event\LifecycleEventArgs $args
     */
    public function processLeaves(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Leaves) {
            $entityManager = $args->getObjectManager();
            $this->processStatusChange($entity, $entityManager);
        }
    }
    
    /**
     * Gets new status value saved in leave and sends emails accordingly
     * @param Leaves $leaves
     * @param \Doctrine\Common\Persistence\ObjectManager $entityManager
     */
    private function processStatusChange($leaves, $entityManager)
    {
        $this->updateLeaveBalance($leaves, $entityManager);
        $this->sendEmail($leaves, $entityManager);
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
            list($leaveType, $previousBalance, $newBalance) = $this->updateUserBalance($leaves, $user);

            $entityManager->persist(new BalanceLog($user, $leaveType, $previousBalance, $newBalance, $currentUser, $leaves->getStatus()));
            $entityManager->persist($user);
            $entityManager->flush();
        }
    }
    
    /**
     * @param boolean $allSupervisors
     *
     * @return array
     */
    private function getSupervisorsEmails($allSupervisors = false): array
    {
        $emailTo = [];
        $user = $this->getUser();
        if ($user) {
            $supervisors = $user->getSupervisorsLevel1();
            if ($supervisors) {
                foreach ($supervisors as $supervisor) {
                    $emailTo[] = $supervisor->getEmail();
                }
            }
            if ($allSupervisors) {
                $supervisors = $user->getSupervisorsLevel2();
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
     * @param $leaves
     * @param $entityManager
     */
    private function sendEmail($leaves, $entityManager): void
    {
        $templateName = $leaves->getStatus();
        $emailTo = [];
        $allSupervisors = [];
        // mail to supervisors level 1
        if ($templateName == $leaves::STATUS_REQUESTED) { //|| $templateName == $leaves::STATUS_WITHDRAWN
            $emailTo = $this->getSupervisorsEmails(TRUE);
        }
        // mail to leave applicant, cc all supervisors
        else {
            $emailTo[] = $leaves->getUser()->getEmail();
            $allSupervisors = $this->getSupervisorsEmails(TRUE);
        }
        
        if ($emailTo) {
            $template = $entityManager->getRepository('LeavesOvertimeBundle:EmailTemplate')
                ->findOneBy(['name' => $templateName]);
            if ($template) {
                $emailOptions = [
                    'subject' => sprintf('Leave %s', $templateName),
                    'from' => $this->container->getParameter('from_email'),
                    'to' => $emailTo,
                    'body' => $template->getContent(),
                ];
                if ($allSupervisors) {
                    $emailOptions['cc'] = $allSupervisors;
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
        return [$isSickLeave ? $leaves::TYPE_SICK_LEAVE : $leaves::TYPE_LOCAL_LEAVE, $previousBalance, $newBalance];
    }
}