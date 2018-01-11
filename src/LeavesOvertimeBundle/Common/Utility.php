<?php

namespace LeavesOvertimeBundle\Common;

class Utility {
    public function sendSwiftMail(\Swift_Mailer $mailer, $emailOptions)
    {
        $message = (new \Swift_Message($emailOptions['subject']))
            ->setFrom($emailOptions['from'])
            ->setTo($emailOptions['to'])
            ->setBody($emailOptions['body'])
        ;
        $mailer->send($message);
    }
}