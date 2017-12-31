<?php

namespace LeavesOvertimeBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class AuditSubscriber implements EventSubscriber
{
    private $validClasses = [
        'LeavesOvertimeBundle\Entity\Employee',
        'LeavesOvertimeBundle\Entity\JobTitle',
        'LeavesOvertimeBundle\Entity\BusinessUnit',
        'LeavesOvertimeBundle\Entity\Department',
        'LeavesOvertimeBundle\Entity\Project',
        'LeavesOvertimeBundle\Entity\EmailTemplate',
        'LeavesOvertimeBundle\Entity\PublicHoliday',
    ];
    private $context;
    
    public function __construct($securityContext){
        $this->context= $securityContext;
    }
    
    private function getUser(){
        if ($this->context->getToken() != null) {
            return $this->context->getToken()->getUser();
        }
        return 'system';
    }
    
    public function isValidClass($object, $classNames)
    {
        foreach ($classNames as $className) {
            if ($object instanceof $className) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }
    
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($this->isValidClass($entity, $this->validClasses)) {
            $entity->setUpdatedAt(new \DateTime());
            $entity->setUpdatedBy($this->getUser());
        }
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($this->isValidClass($entity, $this->validClasses)) {
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy($this->getUser());
        }
    }
    
}