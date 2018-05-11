<?php

namespace LeavesOvertimeBundle\Validator\Constraints;

use LeavesOvertimeBundle\Entity\BalanceLog;
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

        $leaveType = $leaves->getType();
        $user = $leaves->getUser();
        $duration = $leaves->getDuration();
        if ($leaveType == $leaves::TYPE_SICK_LEAVE || $leaveType == $leaves::TYPE_LOCAL_LEAVE) {
            $isSickLeave = $leaveType == $leaves::TYPE_SICK_LEAVE;
            $currentBalance = $isSickLeave ? $user->getSickBalance() : $user->getTotalLocalBalance();
            $isApprovedStatus = $leaves->getStatus() == $leaves::STATUS_APPROVED;
            $newBalance = $isApprovedStatus ? $currentBalance - $duration : $currentBalance + $duration;
            if ($newBalance < 0) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter("%message%", 'This operation would result in a negative balance, thus invalid.')
                    ->addViolation();
            }
        }


        // check yearly limits per leave type

        $yearlyLimit = 0;
        switch ($leaveType) {
            case $leaves::TYPE_MATERNITY_LEAVE:
            case $leaves::TYPE_MATERNITY_LEAVE_WITHOUT_PAY:
                $yearlyLimit = 98;
                break;
            case $leaves::TYPE_PATERNITY_LEAVE:
            case $leaves::TYPE_PATERNITY_LEAVE_WITHOUT_PAY:
                $yearlyLimit = 5;
                break;
            case $leaves::TYPE_COMPASSIONATE_LEAVE:
                $yearlyLimit = 2;
                break;
            case $leaves::TYPE_WEDDING_LEAVE:
                $yearlyLimit = 5;
                break;
            case $leaves::TYPE_INJURY_LEAVE:
                $yearlyLimit = 14;
                break;
            case $leaves::TYPE_SICK_LEAVE:
                $yearlyLimit = $user->getUserType() == 'Office attendant' ? 21 : 15;
                break;
            default:
                break;
        }

        if (!$yearlyLimit) {
            return;
        }

        $userBalanceLogs = $user->getBalanceLogs();
        if (!$userBalanceLogs) {
            return;
        }

        $leaveAmountTaken = $this->getLeaveAmountTaken($leaves, $userBalanceLogs, $leaveType);
        if ($leaveAmountTaken + $duration > $yearlyLimit) {
            $this->context->buildViolation($constraint->message)
                ->setParameter("%message%", 'This operation would exceed the yearly limit on this leave type, thus invalid.')
                ->addViolation();
        }
    }

    /**
     * @param $leaves
     * @param $userBalanceLogs
     * @param $leaveType
     * @return int
     */
    public function getLeaveAmountTaken($leaves, $userBalanceLogs, $leaveType)
    {
        $leaveAmountTaken = 0;
        /** @var BalanceLog $userBalanceLog */
        foreach ($userBalanceLogs as $userBalanceLog) {
            if (!($userBalanceLog->getType() == $leaveType && $userBalanceLog->getLeave()->getStatus() == $leaves::STATUS_APPROVED)) {
                continue;
            }

            // matches leave type and approved, check was created current year
            $dateFormat = 'Y';
            $balanceLogDate = $userBalanceLog->getCreatedAt() instanceof \DateTime
                ? $userBalanceLog->getCreatedAt()->format($dateFormat) : null;
            $currentDate = date($dateFormat);
            if ($balanceLogDate == $currentDate) {
                $leaveAmountTaken++;
            }
        }

        return $leaveAmountTaken;
    }
}