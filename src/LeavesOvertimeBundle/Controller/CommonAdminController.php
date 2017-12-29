<?php

namespace LeavesOvertimeBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

class CommonAdminController extends CRUDController
{
    public $entity;
    
    public function __construct($entity = null) {
        $this->entity = $entity;
    }
    
    public function preDelete(Request $request, $object) {
        if (!$request->request->get('skip')) {
            $linkedEmployees = $this->getDoctrine()
                ->getRepository($this->entity)
                ->findReferencedEmployees($object->getId(), lcfirst(basename($this->entity)));
            
            if (!empty($linkedEmployees)) {
                return $this->renderWithExtraParams('LeavesOvertimeBundle:SonataAdmin:predelete.html.twig', [
                    'object' => $object,
                    'action' => 'delete',
                    'csrf_token' => $this->getCsrfToken('sonata.delete'),
                    'linkedEmployees' => $linkedEmployees
                ]);
            }
        }
    }
}
