<?php

namespace LeavesOvertimeBundle\Controller;

use LeavesOvertimeBundle\Entity\JobTitle;

class JobTitleAdminController extends CommonAdminController
{
    public function __construct($entity = null) {
        parent::__construct(JobTitle::class);
    }
}
