<?php

namespace LeavesOvertimeBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;

class DepartmentAdminController extends CRUDController
{
    public function preDelete(Request $request, $object) {
        parent::preDelete($request, $object); // TODO: Change the autogenerated stub
        dump($object);
    }
}
