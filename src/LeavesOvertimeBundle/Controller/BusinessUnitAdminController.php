<?php

namespace LeavesOvertimeBundle\Controller;

use LeavesOvertimeBundle\Entity\BusinessUnit;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

class BusinessUnitAdminController extends CRUDController
{
//    public function deleteAction($id)
//    {
//        try {
//            return parent::deleteAction($id);
//        } catch (\Doctrine\DBAL\DBALException $e) {
//            $this->get('session')->setFlash('sonata_flash_error', 'Cannot delete object'));
//        }
//        return new RedirectResponse($this->admin->generateUrl('list'));
//    }
    
    public function preDelete(Request $request, $object) {
        if (!$request->request->get('skip')) {
            $linkedEmployees = $this->getDoctrine()
                ->getRepository(BusinessUnit::class)
                ->findReferencedEmployees($object->getId());
            
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
