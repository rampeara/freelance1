<?php

namespace LeavesOvertimeBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use LeavesOvertimeBundle\Common\Utility;
use LeavesOvertimeBundle\Entity\Leaves;
use Doctrine\ORM\Event;

class LeavesListener
{
    private $container;
    private $context;
    
    public function __construct($securityContext, $container){
        $this->context= $securityContext;
        $this->container = $container;
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
    
    private function processStatusChange($entity, UnitOfWork $unitOfWork, EntityManager $entityManager) {
        if ($entity instanceof Leaves) {
            $changeset = $unitOfWork->getEntityChangeSet($entity);
        
            if (!is_array($changeset)) {
                return null;
            }
        
            if (array_key_exists('status', $changeset)) {
                $changes = $changeset['status'];
            
                $previousValueForField = array_key_exists(0, $changes) ? $changes[0] : null;
                $newValueForField = array_key_exists(1, $changes) ? $changes[1] : null;
            
                if ($previousValueForField != $newValueForField) {
                    if ($newValueForField != 'Pending') {
                        $templateName = $newValueForField;
                    }
                    else {
                        $templateName = 'Requested';
                    }
                    
                    $emailTo = [];
                    // send mail to supervisors level 1
                    if ($templateName == 'Requested' || $templateName == 'Withdrawn') {
                        $emailTo = $this->getSupervisorsEmails($emailTo);
                    }
                    // send mail to leave applicant
                    else {
                        $emailTo[] = $entity->getUser()->getEmail();
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
                            $utility = new Utility();
                            $utility->sendSwiftMail($this->container->get('swiftmailer.mailer'), $emailOptions);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * @param $emailTo
     *
     * @return array
     */
    private function getSupervisorsEmails($emailTo): array {
        $user = $this->getUser();
        if ($user) {
            $supervisors = $user->getSupervisorsLevel1();
            if ($supervisors) {
                foreach ($supervisors as $supervisor) {
                    $emailTo[] = $supervisor->getEmail();
                }
            }
        }
        return $emailTo;
    }
}