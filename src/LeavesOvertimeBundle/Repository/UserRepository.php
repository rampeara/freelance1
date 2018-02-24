<?php

namespace LeavesOvertimeBundle\Repository;

use Doctrine\ORM\ORMException;
use LeavesOvertimeBundle\Entity\BalanceLog;
use LeavesOvertimeBundle\Entity\Leaves;

class UserRepository extends \Doctrine\ORM\EntityRepository
{
 
    public $balanceLog;
    
    public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->balanceLog = new BalanceLog();
    }
    
    /**
     * Returns users sorted by lastname ASC, and removes logged in user if
     * $user is passed
     * @param \Application\Sonata\UserBundle\Entity\User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUsersQueryBuilder($user = null)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->orderBy('u.lastname', 'ASC')
            ->orderBy('u.firstname', 'ASC');
        if ($user) {
            $queryBuilder->where('u.email != :email')
                ->setParameter('email', $user->getEmail());
        }
        return $queryBuilder;
    }
    
    /**
     * Returns unique ids of given user and their subordinates as a supervisor
     * lvl 1 AND 2
     * @param $userId
     *
     * @return array
     */
    public function getMySubordinatesIds($userId)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->select('u.id')
            ->innerJoin('u.supervisorsLevel1', 'sp1')
            ->where('sp1.id = :id')
            ->setParameter('id', $userId)
        ;
        $mySubordinatesAsLevel1 = $queryBuilder->getQuery()->getScalarResult();
    
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->select('u.id')
            ->innerJoin('u.supervisorsLevel2', 'sp2')
            ->where('sp2.id = :id')
            ->setParameter('id', $userId)
        ;
        $mySubordinatesAsLevel2 = $queryBuilder->getQuery()->getScalarResult();
        
        $ids1 = array_column($mySubordinatesAsLevel1, "id");
        $ids2 = array_column($mySubordinatesAsLevel2, "id");

        return array_unique(array_merge([(string)$userId], $ids1, $ids2));
    }
    
    public function getFilteredUsersQueryBuilder($userId)
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->orderBy('u.lastname', 'ASC')
            ->orderBy('u.firstname', 'ASC')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $this->getMySubordinatesIds($userId))
        ;
        return $queryBuilder;
    }
    
    /**
     * Return array of users over 1 year service
     * @return mixed
     */
    public function getUsersOver1YearService()
    {
        $date = new \DateTime('-1 year');
        $date = $date->format('Y-m-d');
        return $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('ApplicationSonataUserBundle:User', 'u')
            ->where(':date > u.hireDate')
            ->andWhere('u.departureDate IS NULL')
            ->andWhere('u.enabled = 1')
            ->orderBy('u.hireDate', 'ASC')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Includes filter on isNoProbationLeaves != 1
     * @return mixed
     */
    public function getUsersUnder1YearService()
    {
        $date = new \DateTime('-1 year');
        $date = $date->format('Y-m-d');
        return $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('ApplicationSonataUserBundle:User', 'u')
            ->where(':date <= u.hireDate')
            ->andWhere('u.isNoProbationLeaves != 1')
            ->andWhere('u.departureDate IS NULL')
            ->andWhere('u.enabled = 1')
            ->orderBy('u.hireDate', 'ASC')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * All active, still employed users
     * @return mixed
     */
    public function getAllActiveUsers()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('ApplicationSonataUserBundle:User', 'u')
            ->andWhere('u.departureDate IS NULL')
            ->andWhere('u.enabled = 1')
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * Scheduled tasks
     */
    
    /**
     * Used by scheduled task to find and increment local & sick balance of employees on probation period
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function incrementBalancesForProbationAccounts()
    {
        $leaves = new Leaves();
        $invalidLeaveType = [
            $leaves::TYPE_ABSENCE_FROM_WORK,
            $leaves::TYPE_INJURY_LEAVE_WITHOUT_PAY,
            $leaves::TYPE_LEAVE_WITHOUT_PAY,
            $leaves::TYPE_MATERNITY_LEAVE_WITHOUT_PAY,
            $leaves::TYPE_PATERNITY_LEAVES_WITHOUT_PAY,
            $leaves::TYPE_WEDDING_LEAVE_WITHOUT_PAY,
        ];
        
        // not yet 1 year in company
        $usersLessThanAYear = $this->getUsersUnder1YearService();
        if (empty($usersLessThanAYear)) {
            return;
        }
        
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($usersLessThanAYear as $user) {
            if (!$this->isHireDateValid($user)) {
                continue;
            }

            $hireDate = $user->getHireDate();
            $monthsOfService = $hireDate->diff(new \DateTime('now'))->m;
            $yearsOfService = $hireDate->diff(new \DateTime('now'))->y;
            if (!($monthsOfService > 6 || $yearsOfService == 1)) {
                continue;
            }
    
            if (!$this->isDatePartMatchingToday($hireDate))
            {
                continue;
            }
            
            $userLeaves = $user->getLeaves();
            $noProbationLeaves = false;
            /** @var Leaves $userLeave */
            foreach ($userLeaves as $userLeave) {
                if (in_array($userLeave->getType(), $invalidLeaveType)) {
                    $noProbationLeaves = true;
                    break;
                }
            }
            if ($noProbationLeaves) {
                // user will only get leaves after 1 year completion
                // set no probation leaves to true so that next time the search result doesn't take this user into account
                $user->setIsNoProbationLeaves($noProbationLeaves);
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();
                continue;
            }
            
            $balanceLogTypeLocal = $this->balanceLog::TYPE_PROBATION_LOCAL_LEAVE;
            $balanceLogTypeSick = $this->balanceLog::TYPE_PROBATION_SICK_LEAVE;
            if ($this->hasReceivedLeaveTypeThisDateFormat($user, [$balanceLogTypeLocal, $balanceLogTypeSick])) {
                continue;
            }
            
            // all conditions satisfied, increment & log
            $oldLocalBalance = $user->getLocalBalance();
            $oldSickBalance = $user->getSickBalance();
            $user->incrementLocalLeave();
            $user->incrementSickLeave();
            $localDescription = sprintf($this->balanceLog::TYPE_PROBATION_LOCAL_LEAVE_DESC, $oldLocalBalance, $user->getLocalBalance());
            $sickDescription = sprintf($this->balanceLog::TYPE_PROBATION_SICK_LEAVE_DESC, $oldSickBalance, $user->getSickBalance());
            $balanceLogLocal = new BalanceLog($localDescription, $user, 'system', $balanceLogTypeLocal);
            $balanceLogSick = new BalanceLog($sickDescription, $user, 'system', $balanceLogTypeSick);
            
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->persist($balanceLogLocal);
            $this->getEntityManager()->persist($balanceLogSick);
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Used by scheduled task to find and increment local & sick balance of employees annually
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function incrementBalancesForAnnualAccounts()
    {
        $usersMoreThanAYear = $this->getUsersOver1YearService();
        if (empty($usersMoreThanAYear)) {
            return;
        }
        
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($usersMoreThanAYear as $user) {
            if (!$this->isHireDateValid($user)) {
                continue;
            }
            
            $balanceLogTypeLocal = $this->balanceLog::TYPE_ANNUAL_LOCAL_LEAVE;
            $balanceLogTypeSick = $this->balanceLog::TYPE_ANNUAL_SICK_LEAVE;
            if ($this->hasReceivedLeaveTypeThisDateFormat($user, [$balanceLogTypeLocal, $balanceLogTypeSick], 'Y-m-d')) {
                continue;
            }
            
            // all conditions satisfied, increment & log
            $oldLocalBalance = $user->getLocalBalance();
            $oldSickBalance = $user->getSickBalance();
            list($localAmount, $sickAmount) = $this->getLeaveAmountsByCriteria($user);
            $user->incrementLocalLeave($localAmount);
            // max of 90 sick at all times
            if ($oldSickBalance != 90) {
                $user->incrementSickLeave($sickAmount);
            }
            if ($user->getSickBalance() > 90) {
                $user->setSickBalance(90);
            }
    
            $localDescription = sprintf($this->balanceLog::TYPE_ANNUAL_LOCAL_LEAVE_DESC, $oldLocalBalance, $user->getLocalBalance());
            $sickDescription = sprintf($this->balanceLog::TYPE_ANNUAL_SICK_LEAVE_DESC, $oldSickBalance, $user->getSickBalance());
            $balanceLogLocal = new BalanceLog($localDescription, $user, 'system', $balanceLogTypeLocal);
            $balanceLogSick = new BalanceLog($sickDescription, $user, 'system', $balanceLogTypeSick);

            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->persist($balanceLogLocal);
            $this->getEntityManager()->persist($balanceLogSick);
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Used to transfer local balance to carry forward local balance for users > 1 year of service
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function carryForwardLocalBalance()
    {
        // run only on end of 31 Dec, time in schedule task
//        if (date('m-d') != '12-31') {
//            return;
//        }
        
        $users = $this->getUsersOver1YearService();
        if (empty($users)) {
            return;
        }
    
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($users as $user) {
            $localBalance = $user->getLocalBalance();
            $carryForwardLocalBalance = $user->getCarryForwardLocalBalance();
            $user->setCarryForwardLocalBalance($carryForwardLocalBalance + $localBalance);
            $user->setLocalBalance(0);
            $logDescription = sprintf($this->balanceLog::TYPE_CARRY_FORWARD_LOCAL_BALANCE_DESC, $localBalance, $user->getLocalBalance(), $carryForwardLocalBalance, $user->getCarryForwardLocalBalance());
    
            $this->getEntityManager()->persist(new BalanceLog($logDescription, $user, 'system', $this->balanceLog::TYPE_CARRY_FORWARD_LOCAL_BALANCE));
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to transfer local balance to frozen local balance for users < 1 year of service
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function freezeLocalBalance()
    {
        // run only on end of 31 Dec, time in schedule task
//        if (date('m-d') != '12-31') {
//            return;
//        }
        
        $users = $this->getUsersUnder1YearService();
        if (empty($users)) {
            return;
        }
        
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($users as $user) {
            $localBalance = $user->getLocalBalance();
            $frozenLocalBalance = $user->getFrozenLocalBalance();
            $user->setFrozenLocalBalance($frozenLocalBalance + $localBalance);
            $user->setLocalBalance(0);
            $logDescription = sprintf($this->balanceLog::TYPE_FREEZE_LOCAL_BALANCE_DESC, $localBalance, $user->getLocalBalance(), $frozenLocalBalance, $user->getFrozenLocalBalance());
    
            $this->getEntityManager()->persist(new BalanceLog($logDescription, $user, 'system', $this->balanceLog::TYPE_FREEZE_LOCAL_BALANCE));
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Used to transfer carry forward local balance to frozen carry forward local balance
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function freezeCarryForwardLocalBalance()
    {
        // run only on end of 31 March, time in schedule task
        //        if (date('m-d') != '03-31') {
        //            return;
        //        }
        
        $users = $this->getAllActiveUsers();
        if (empty($users)) {
            return;
        }
        
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($users as $user) {
            $carryForwardLocalBalance = $user->getCarryForwardLocalBalance();
            $frozenCarryForwardLocalBalance = $user->getFrozenCarryForwardLocalBalance();
            $user->setFrozenCarryForwardLocalBalance($frozenCarryForwardLocalBalance + $carryForwardLocalBalance);
            $user->setCarryForwardLocalBalance(0);
            $logDescription = sprintf($this->balanceLog::TYPE_FREEZE_CARRY_FORWARD_LOCAL_BALANCE_DESC, $carryForwardLocalBalance, $user->getCarryForwardLocalBalance(), $frozenCarryForwardLocalBalance, $user->getFrozenCarryForwardLocalBalance());
            
            $this->getEntityManager()->persist(new BalanceLog($logDescription, $user, 'system', $this->balanceLog::TYPE_FREEZE_CARRY_FORWARD_LOCAL_BALANCE));
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
    }
    
    /**
     * Helpers
     */
    
    /**
     * @param \Application\Sonata\UserBundle\Entity\User $user
     *
     * @return bool
     */
    private function isHireDateValid($user)
    {
        if (!$user && !$user->getHireDate() && empty($user->getHireDate())) {
            return false;
        }
        return true;
    }
    
    /**
     * @param $hireDate
     *
     * @return bool
     */
    private function isDatePartMatchingToday($hireDate)
    {
        if (!$hireDate instanceof \DateTime) {
            return false;
        }
        if ($hireDate->format('d') != date('d')) {
            // skip not matching condition only if hire date 29 Feb, current year is not leap year and current date is 28 Feb
            $isLeapYear = date('L');
            if (!($hireDate->format('m-d') == '02-29' && !$isLeapYear && date('m-d') == '02-28')) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @param \Application\Sonata\UserBundle\Entity\User $user
     * @param array $leaveTypes
     * @param string $dateFormat
     *
     * @return bool|\DateTime
     */
    private function hasReceivedLeaveTypeThisDateFormat($user, $leaveTypes, $dateFormat = 'Y-m')
    {
        $userBalanceLogs = $user->getBalanceLogs();
        $leaveTypeReceived = false;
        if (!$userBalanceLogs) {
            return $leaveTypeReceived;
        }
        /** @var BalanceLog $userBalanceLog */
        foreach ($userBalanceLogs as $userBalanceLog) {
            $balanceLogDate = $userBalanceLog->getCreatedAt() instanceof \DateTime
                ? $userBalanceLog->getCreatedAt()->format($dateFormat) : null;
            $currentDate = new \DateTime('now');
            $currentDate = $currentDate->format($dateFormat);
            if (
                $balanceLogDate == $currentDate &&
                ($userBalanceLog->getType() == $leaveTypes[0]
                || $userBalanceLog->getType() == $leaveTypes[1])
            ) {
                return true;
            }
        }
        
        return $leaveTypeReceived;
    }
    
    /**
     * @param \Application\Sonata\UserBundle\Entity\User $user
     *
     * @return array
     */
    private function getLeaveAmountsByCriteria($user)
    {
        $id = 0;
        if ($user->getJobTitle()) {
            $id = $user->getJobTitle()->getId();
        }
        switch($id) {
            // support comex sub group
            case 15:
            case 16:
            case 25:
            case 26:
                $localAmount = 25;
                $sickAmount = 15;
                break;
            // support attendant
            case 38:
                $localAmount = 16;
                $sickAmount = 21;
                break;
            default:
                $localAmount = 22;
                $sickAmount = 15;
        }
        
        // after first year of service and before 31 Dec is pro-rated
        $currentYear = date('Y');
        $hireDate = $user->getHireDate();
        $hireYear = $hireDate->format('Y');
        if ($hireYear == $currentYear - 1) {
            $endOfYear = new \DateTime(sprintf('%s-12-31', $currentYear));
            $monthsTillEOY = $hireDate->diff($endOfYear)->m;
            $localAmount = ($localAmount / 12) * $monthsTillEOY;
            $sickAmount = ($sickAmount / 12) * $monthsTillEOY;
            // 2 decimal places
            $localAmount = round($localAmount, 2);
            $sickAmount = round($sickAmount, 2);
        }
        
        return [$localAmount, $sickAmount];
    }
}
