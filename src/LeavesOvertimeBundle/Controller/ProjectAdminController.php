<?php

namespace LeavesOvertimeBundle\Controller;

use LeavesOvertimeBundle\Entity\Project;

class ProjectAdminController extends CommonAdminController
{
    public function __construct($entity = null) {
        parent::__construct(Project::class);
    }
}
