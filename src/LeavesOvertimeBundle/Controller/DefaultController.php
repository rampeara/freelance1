<?php

namespace LeavesOvertimeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LeavesOvertimeBundle:Default:index.html.twig');
    }
}
