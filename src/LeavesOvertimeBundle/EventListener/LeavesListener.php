<?php

namespace LeavesOvertimeBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use LeavesOvertimeBundle\Common\Utility;
use LeavesOvertimeBundle\Entity\Leaves;
use Doctrine\ORM\Event;

class LeavesListener extends Utility
{
    private $context;
    
    public function __construct($securityContext, $container) {
        parent::__construct($container);
        $this->context= $securityContext;
    }
    
    /**
     * @return $this|\Application\Sonata\UserBundle\Entity\User
     */
    private function getUser(){
        if ($this->context->getToken() != null) {
            return $this->context->getToken()->getUser();
        }
        return null;
    }
    
    /**
     * Gets all the entities to flush
     *
     * @param Event\OnFlushEventArgs $eventArgs Event args
     *
     * @return null
     * @throws \Doctrine\ORM\ORMException
     */
    public function onFlush(Event\OnFlushEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        
        //Insertions
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $this->processStatusChange($entity, $unitOfWork, $entityManager);
        }
        
        //Updates
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            $this->processStatusChange($entity, $unitOfWork, $entityManager);
        }
    }
    
    /**
     * Gets new status value saved in leave and sends emails accordingly
     * @param Leaves $entity
     * @param \Doctrine\ORM\UnitOfWork $unitOfWork
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    private function processStatusChange($entity, UnitOfWork $unitOfWork, EntityManager $entityManager) {
        $newValueForField = $this->getNewLeaveStatusValue($entity, $unitOfWork);
        if ($newValueForField == null) {
            return;
        }
        
        $templateName = $newValueForField;
        $emailTo = [];
        $allSupervisors = [];
        // send mail to supervisors level 1
        if ($templateName == $entity::STATUS_REQUESTED || $templateName == $entity::STATUS_WITHDRAWN) {
            $emailTo = $this->getSupervisorsEmails();
        }
        // send mail to leave applicant, cc all supervisors
        else {
            $emailTo[] = $entity->getUser()->getEmail();
            $allSupervisors = $this->getSupervisorsEmails(true);
        }
        
        if ($emailTo) {
            $template = $entityManager->getRepository('LeavesOvertimeBundle:EmailTemplate')->findOneBy(['name' => $templateName]);
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
//                $utility = new Utility();
                $this->sendSwiftMail($emailOptions);
            }
        }
    }
    
    /**
     * @param $entity
     * @param \Doctrine\ORM\UnitOfWork $unitOfWork
     *
     * @return null|string
     */
    private function getNewLeaveStatusValue($entity, UnitOfWork $unitOfWork) {
        if (!($entity instanceof Leaves)) {
            return null;
        }
        
        $changeset = $unitOfWork->getEntityChangeSet($entity);
        if (!is_array($changeset)) {
            return null;
        }
        
        if (!(array_key_exists('status', $changeset))) {
            return null;
        }
        
        $changes = $changeset['status'];
        $previousValueForField = array_key_exists(0, $changes) ? $changes[0] : null;
        $newValueForField = array_key_exists(1, $changes) ? $changes[1] : null;
        
        if ($previousValueForField == $newValueForField) {
            return null;
        }
        
        return $newValueForField;
    }
    
    /**
     * @param boolean $allSupervisors
     *
     * @return array
     */
    private function getSupervisorsEmails($allSupervisors = false): array {
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
}