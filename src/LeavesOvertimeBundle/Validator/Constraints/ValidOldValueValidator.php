<?php

namespace LeavesOvertimeBundle\Validator\Constraints;

use LeavesOvertimeBundle\Entity\Leaves;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManager;

class ValidOldValueValidator extends ConstraintValidator
{
    protected $entityManager;
    protected $securityContext;
    protected $leaveTypeLimits;

    public function __construct(EntityManager $entityManager, $securityContext, $leaveTypeLimits)
    {
        $this->entityManager = $entityManager;
        $this->securityContext = $securityContext;
        $this->leaveTypeLimits = $leaveTypeLimits;
    }
    
    /**
     * Various validations before changing the current leave's status
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
        if (!(is_array($oldData) && !empty($oldData))) {
            return;
        }
    
        if ($newValue == null) {
            $this->context->buildViolation($constraint->message)
                ->setParameter("%message%", 'Please select a leave status.')
                ->addViolation();
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

        // check if resulting balance will be negative after approval or cancellation

        $leaveType = $leaves->getType();
        $user = $leaves->getUser();
        $duration = $leaves->getDuration();
        $isApprovedStatus = $leaves->getStatus() == $leaves::STATUS_APPROVED;

        if ($leaveType == $leaves::TYPE_SICK_LEAVE || $leaveType == $leaves::TYPE_LOCAL_LEAVE) {
            $isSickLeave = $leaveType == $leaves::TYPE_SICK_LEAVE;
            $currentBalance = $isSickLeave ? $user->getSickBalance() : $user->getTotalLocalBalance();
            $newBalance = $isApprovedStatus ? $currentBalance - $duration : $currentBalance + $duration;
            if ($newBalance < 0) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter("%message%", 'This operation would result in a negative balance, thus invalid.')
                    ->addViolation();
                return;
            }
        }


        // check yearly limits per leave type

        if (!$isApprovedStatus) {
            return;
        }

        $yearlyLimit = $this->getLeaveYearlyLimit($leaves, $leaveType, $user);
        if (!$yearlyLimit) {
            return;
        }

        $userLeaves = $user->getLeaves();
        if (!$userLeaves) {
            return;
        }

        $currentLeaveId = $leaves->getId();
        $leaveAmountTaken = $this->getSimilarLeaveAmountTaken($userLeaves, $leaveType, $currentLeaveId);
        if ($leaveAmountTaken + $duration > $yearlyLimit) {
            $this->context->buildViolation($constraint->message)
                ->setParameter("%message%", 'This operation would exceed the yearly limit on this leave type, thus invalid.')
                ->addViolation();
            return;
        }
    }

    /**
     * Returns total of similar leaves taken this year
     * @param $userLeaves
     * @param $leaveType
     * @param $currentLeaveId
     * @return int
     */
    public function getSimilarLeaveAmountTaken($userLeaves, $leaveType, $currentLeaveId)
    {
        $leaveAmountTaken = 0;
        /** @var Leaves $userLeaves */
        foreach ($userLeaves as $userLeave) {
            if (!($userLeave->getType() == $leaveType && $userLeave->getStatus() == $userLeave::STATUS_APPROVED && $userLeave->getId() != $currentLeaveId)) {
                continue;
            }

            // matches leave type and is approved, now check was created in current year
            $dateFormat = 'Y';
            $balanceLogDate = $userLeave->getCreatedAt() instanceof \DateTime
                ? $userLeave->getCreatedAt()->format($dateFormat) : null;
            $currentDate = date($dateFormat);
            if ($balanceLogDate == $currentDate) {
                $leaveAmountTaken += $userLeave->getDuration();
            }
        }

        return $leaveAmountTaken;
    }

    /**
     * Returns yearly limit of given leave type
     * @param $leaves
     * @param $leaveType
     * @param $user
     * @return int
     */
    protected function getLeaveYearlyLimit($leaves, $leaveType, $user)
    {
        $yearlyLimit = 0;
        switch ($leaveType) {
            case $leaves::TYPE_MATERNITY_LEAVE:
            case $leaves::TYPE_MATERNITY_LEAVE_WITHOUT_PAY:
                $yearlyLimit = $this->leaveTypeLimits[$leaves::TYPE_MATERNITY_LEAVE];
                break;
            case $leaves::TYPE_PATERNITY_LEAVE:
            case $leaves::TYPE_PATERNITY_LEAVE_WITHOUT_PAY:
                $yearlyLimit = $this->leaveTypeLimits[$leaves::TYPE_PATERNITY_LEAVE];
                break;
            case $leaves::TYPE_COMPASSIONATE_LEAVE:
                $yearlyLimit = $this->leaveTypeLimits[$leaves::TYPE_COMPASSIONATE_LEAVE];
                break;
            case $leaves::TYPE_WEDDING_LEAVE:
//            case $leaves::TYPE_WEDDING_LEAVE_WITHOUT_PAY:
                $yearlyLimit = $this->leaveTypeLimits[$leaves::TYPE_WEDDING_LEAVE];
                break;
            case $leaves::TYPE_INJURY_LEAVE:
//            case $leaves::TYPE_INJURY_LEAVE_WITHOUT_PAY:
                $yearlyLimit = $this->leaveTypeLimits[$leaves::TYPE_INJURY_LEAVE];
                break;
            case $leaves::TYPE_SICK_LEAVE:
                $yearlyLimit = $user->getUserType() == 'Office attendant' ? $this->leaveTypeLimits['Sick leave office attendant'] : $this->leaveTypeLimits[$leaves::TYPE_SICK_LEAVE];
                break;
            default:
                break;
        }
        return $yearlyLimit;
    }
}