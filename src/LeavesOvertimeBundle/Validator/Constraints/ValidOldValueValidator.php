<?php

namespace LeavesOvertimeBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class ValidOldValueValidator extends ConstraintValidator
{
    protected $entityManager;
    protected $securityContext;
    
    public function __construct(EntityManager $entityManager, $securityContext)
    {
        $this->entityManager = $entityManager;
        $this->securityContext = $securityContext;
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
        if (!(is_array($oldData) && !empty($oldData)))
        {
            return;
        }
        
        if ($this->securityContext->getToken() == null) {
            $this->context->buildViolation($constraint->message)
                ->setParameter("%message%", 'An unexpected error has occurred. Please log out, log in and try again.')
                ->addViolation();
            return;
        }
        
        if ($leaves->getUser() == $this->securityContext->getToken()->getUser()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter("%message%", 'You cannot change the status of your own leave application. Please contact your supervisor.')
                ->addViolation();
            return;
        }
        
        $oldValue = $oldData['status'];
        if (($newValue == $leaves::STATUS_APPROVED || $newValue == $leaves::STATUS_REJECTED) && $oldValue != $leaves::STATUS_REQUESTED) {
            $this->context->buildViolation($constraint->message)
                ->setParameter("%message%", sprintf('Only leaves with status %s can be approved or rejected.', $leaves::STATUS_REQUESTED))
                ->addViolation();
            return;
        }
        
        if ($newValue == $leaves::STATUS_CANCELLED && $oldValue != $leaves::STATUS_APPROVED) {
            $this->context->buildViolation($constraint->message)
                ->setParameter("%message%", sprintf('Only leaves with status %s can be cancelled.', $leaves::STATUS_APPROVED))
                ->addViolation();
            return;
        }

        $leaveType = $leaves->getType();
        if ($leaveType == $leaves::TYPE_SICK_LEAVE || $leaveType == $leaves::TYPE_LOCAL_LEAVE) {
            $user = $leaves->getUser();
            $isSickLeave = $leaveType == $leaves::TYPE_SICK_LEAVE;
            $currentBalance = $isSickLeave ? $user->getSickBalance() : $user->getTotalLocalBalance();
            $isApprovedStatus = $leaves->getStatus() == $leaves::STATUS_APPROVED;
            $duration = $leaves->getDuration();
            $newBalance = $isApprovedStatus ? $currentBalance - $duration : $currentBalance + $duration;
            if ($newBalance < 0) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter("%message%", 'This operation would result in a negative balance, thus invalid.')
                    ->addViolation();
            }
        }
    }
}