<?php

namespace LeavesOvertimeBundle\EventListener;

use Application\Sonata\UserBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class AuditSubscriber implements EventSubscriber
{
    private $validClasses = [
        'Application\Sonata\UserBundle\Entity\User',
        'LeavesOvertimeBundle\Entity\JobTitle',
        'LeavesOvertimeBundle\Entity\BusinessUnit',
        'LeavesOvertimeBundle\Entity\Department',
        'LeavesOvertimeBundle\Entity\Project',
        'LeavesOvertimeBundle\Entity\EmailTemplate',
        'LeavesOvertimeBundle\Entity\PublicHoliday',
        'LeavesOvertimeBundle\Entity\Leaves',
        'LeavesOvertimeBundle\Entity\UserImport',
    ];
    private $context;
    
    public function __construct($securityContext){
        $this->context= $securityContext;
    }
    
    /**
     * @return $this|\FOS\UserBundle\Model\UserInterface
     */
    private function getUser(){
        if ($this->context->getToken() != null) {
            return $this->context->getToken()->getUser();
        }
        $user = new User();
        return $user->setUsername('system');
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
            $entity->setUpdatedBy($this->getUser()->getUsername());
        }
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($this->isValidClass($entity, $this->validClasses)) {
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy($this->getUser()->getUsername());
        }
    }
    
}