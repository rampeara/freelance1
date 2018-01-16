<?php

namespace LeavesOvertimeBundle\Common;

class Utility {
    /** @var $container \Symfony\Component\DependencyInjection\ContainerInterface */
    public $container;
    
    public function __construct($container){
        $this->container = $container;
    }
    
    public function sendSwiftMail($emailOptions)
    {
        $mailer = $this->container->get('swiftmailer.mailer');
        $message = (new \Swift_Message($emailOptions['subject']))
            ->setFrom($emailOptions['from'])
            ->setTo($emailOptions['to'])
            ->setBody($emailOptions['body'])
        ;
        if (array_key_exists('cc', $emailOptions)) {
            $message->setCc($emailOptions['cc']);
        }
        $mailer->send($message);
    }
}