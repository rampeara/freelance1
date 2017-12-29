<?php

namespace LeavesOvertimeBundle\Controller;

use LeavesOvertimeBundle\Entity\BusinessUnit;

class BusinessUnitAdminController extends CommonAdminController
{
    public function __construct($entity = null) {
        parent::__construct(BusinessUnit::class);
    }
}
