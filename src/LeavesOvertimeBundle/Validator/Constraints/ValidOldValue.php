<?php

namespace LeavesOvertimeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidOldValue extends Constraint
{
    public $message = 'Only leaves with status %status% can be cancelled.';
    
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
    
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}