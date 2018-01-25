<?php

namespace LeavesOvertimeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class ValidOldValueValidator extends ConstraintValidator
{
    protected $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Checks previous status was Approved before allowing to change it to Cancelled
     * @param \LeavesOvertimeBundle\Entity\Leaves $leaves $leaves
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($leaves, Constraint $constraint)
    {
        $newValue = $leaves->getStatus();
        $oldData = $this->entityManager
            ->getUnitOfWork()
            ->getOriginalEntityData($leaves);
    
        // $oldData is empty if we create a new Leaves object.
        if (is_array($oldData) and !empty($oldData))
        {
            $oldValue = $oldData['status'];
            if ($newValue == $leaves::STATUS_CANCELLED && $oldValue != $leaves::STATUS_APPROVED) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter("%status%", $leaves::STATUS_APPROVED)
                    ->addViolation();
            }
        }
    }
}