<?php

namespace LeavesOvertimeBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping;
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
            ->orderBy('u.hireDate', 'ASC')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
        ;
    }
    
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
            $balanceLogLocal = new BalanceLog($oldLocalBalance, $user->getLocalBalance(), $user, 'system', $balanceLogTypeLocal);
            $balanceLogSick = new BalanceLog($oldSickBalance, $user->getSickBalance(), $user, 'system', $balanceLogTypeSick);
            
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
           
            if (!$this->isTodayValidAnnualLeaveDate($user)) {
                continue;
            }
            
            $balanceLogTypelocal = $this->balanceLog::TYPE_ANNUAL_LOCAL_LEAVE;
            $balanceLogTypesick = $this->balanceLog::TYPE_ANNUAL_SICK_LEAVE;
            if ($this->hasReceivedLeaveTypeThisDateFormat($user, [$balanceLogTypelocal, $balanceLogTypesick], 'Y-m-d')) {
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
            
            $balanceLogLocal = new BalanceLog($oldLocalBalance, $user->getLocalBalance(), $user, 'system', $balanceLogTypelocal);
            $balanceLogSick = new BalanceLog($oldSickBalance, $user->getSickBalance(), $user, 'system', $balanceLogTypesick);
            
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->persist($balanceLogLocal);
            $this->getEntityManager()->persist($balanceLogSick);
            $this->getEntityManager()->flush();
        }
    }
    
    public function carryForwardLocalBalance()
    {
        // run only on end of 31 Dec, time in schedule task
        if (date('m-d') != '12-31') {
            return;
        }
        
        $usersMoreThanAYear = $this->getUsersOver1YearService();
        if (empty($usersMoreThanAYear)) {
            return;
        }
    
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($usersMoreThanAYear as $user) {
            $localBalance = $user->getLocalBalance();
            $user->setCarryForwardLocalBalance($localBalance);
            $user->setLocalBalance(0);
            
            $this->getEntityManager()->persist(new BalanceLog($localBalance, 0, $user, 'system', $this->balanceLog::TYPE_CARRY_FORWARD_LOCAL_BALANCE));
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
    }
    
    public function freezeCarryForwardLocalBalance()
    {
        // run only on end of 31 Dec, time in schedule task
        if (date('m-d') != '03-31') {
            return;
        }
        
        $usersMoreThanAYear = $this->getUsersOver1YearService();
        if (empty($usersMoreThanAYear)) {
            return;
        }
        
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($usersMoreThanAYear as $user) {
            $carryForwardLocalBalance = $user->getCarryForwardLocalBalance();
            $user->setFrozenCarryForwardLocalBalance($carryForwardLocalBalance);
            $user->setCarryForwardLocalBalance(0);
            
            $this->getEntityManager()->persist(new BalanceLog($carryForwardLocalBalance, 0, $user, 'system', $this->balanceLog::TYPE_FREEZE_CARRY_FORWARD_LOCAL_BALANCE));
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
    }
    
    public function freezeLocalBalance()
    {
        // run only on end of 31 Dec, time in schedule task
        if (date('m-d') != '12-31') {
            return;
        }
        
        $usersUnderAYear = $this->getUsersUnder1YearService();
        if (empty($usersUnderAYear)) {
            return;
        }
        
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($usersUnderAYear as $user) {
            $localBalance = $user->getLocalBalance();
            $user->setFrozenLocalBalance($localBalance);
            $user->setLocalBalance(0);
            
            $this->getEntityManager()->persist(new BalanceLog($localBalance, 0, $user, 'system', $this->balanceLog::TYPE_FREEZE_LOCAL_BALANCE));
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
    }
    
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
            if (!($hireDate->format('m-d') == '02-29' && date('m-d') == '02-28')) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * @param \Application\Sonata\UserBundle\Entity\User $user
     *
     * @param array $leaveTypes
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
     * @return bool
     * @throws \Exception
     */
    private function isTodayValidAnnualLeaveDate($user)
    {
        $hireDate = $user->getHireDate();
        $yearsOfService = $hireDate->diff(new \DateTime('now'))->y;
        $time = strtotime($hireDate->format('Y-m-d'));
        $time1Month = strtotime('+1 month', $time);
        $time1Year = strtotime(sprintf('+%s year', $yearsOfService), $time1Month);
        $dateofAnnualLeaves = date('Y-m-d', $time1Year);
        $currentDate = date('Y-m-d');
        
        if ($dateofAnnualLeaves == $currentDate) {
            return true;
        }
        return false;
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
        
        $currentMonthNumber = new \DateTime('now');
        $currentMonthNumber = $currentMonthNumber->format('n');
        $localAmount = ((12 - ($currentMonthNumber - 1)) / 12) * $localAmount;
        $sickAmount = ((12 - ($currentMonthNumber - 1)) / 12) * $sickAmount;
        
        return [$localAmount, $sickAmount];
    }
}
