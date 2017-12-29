<?php

namespace LeavesOvertimeBundle\Controller;

use LeavesOvertimeBundle\Entity\Department;

class DepartmentAdminController extends CommonAdminController
{
    public function __construct($entity = null) {
        parent::__construct(Department::class);
    }
}
