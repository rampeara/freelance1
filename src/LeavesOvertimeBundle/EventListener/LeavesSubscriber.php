<?php

namespace LeavesOvertimeBundle\EventListener;

use LeavesOvertimeBundle\Common\Utility;
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
        );
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
            if ($leaves->getStatus() == $leaves::STATUS_APPROVED) {
                if ($leaves->getType() == $leaves::TYPE_SICK_LEAVE) {
                    $user->setSickBalance($user->getSickBalance() - $leaves->getDuration());
                }
                else {
                    $user->setLocalBalance($user->getLocalBalance() - $leaves->getDuration());
                }
            }
            else {
                if ($leaves->getStatus() == $leaves::STATUS_CANCELLED) {
                    if ($leaves->getType() == $leaves::TYPE_SICK_LEAVE) {
                        $user->setSickBalance($user->getSickBalance() + $leaves->getDuration());
                    }
                    else {
                        $user->setLocalBalance($user->getLocalBalance() + $leaves->getDuration());
                    }
                }
            }
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
        if ($templateName == $leaves::STATUS_REQUESTED || $templateName == $leaves::STATUS_WITHDRAWN) {
            $emailTo = $this->getSupervisorsEmails();
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
}