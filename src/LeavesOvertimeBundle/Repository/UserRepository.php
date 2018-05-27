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
     * Returns users under 1 year of service
     * @param $includeAbscence boolean DEFAULT FALSE
     * @return mixed
     */
    public function getUsersUnder1YearService($includeAbscence = false)
    {
        $date = new \DateTime('-1 year');
        $date = $date->format('Y-m-d');
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('ApplicationSonataUserBundle:User', 'u')
            ->where(':date <= u.hireDate')
            ->orderBy('u.hireDate', 'DESC')
            ->setParameter('date', $date)
        ;

        if ($includeAbscence) {
            // a little inefficient put no better choice as Doctrine doesn't support INTERVAL
            $qb->orWhere('u.lastAbsenceDate IS NOT NULL');
        }

        return $qb->andWhere('u.departureDate IS NULL')
            ->andWhere('u.enabled = 1')->getQuery()->getResult();
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
        // not yet 1 year in company
        $includeAbscence = true;
        $usersLessThanAYear = $this->getUsersUnder1YearService($includeAbscence);
        if (empty($usersLessThanAYear)) {
            return;
        }
        
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($usersLessThanAYear as $user) {
            if (!$this->isHireDateValid($user)) {
                continue;
            }

            $hireDate = $user->getHireDate();
            // remove invalid entries with abscence date as couldn't properly filter in DQL
            $oneYearAgo = new \DateTime('-1 year');
            if (!($oneYearAgo->setTime(0,0,0) <= $hireDate)) {
                continue;
            }

            $absenceDate = $user->getLastAbsenceDate();
            // check if absence date found and it was within hire date + 1 year, if yes take its following day as valid date
            if ($absenceDate instanceof \DateTime
                && $this->isDateWithinAYear($absenceDate, $hireDate)) {
                // CAUTION: modify changed source date, if persisted this must be changed to a clone like for hireDate
                $absenceDate = $absenceDate->modify('+1 day');
            }
            else {
                $absenceDate = null;
            }

            $hireOrResetDate = $absenceDate ? $absenceDate : $hireDate;
            $monthsOfService = $hireOrResetDate->diff(new \DateTime('now'))->m;
            $hireDateAdd1Year = clone $hireDate;
            $hireDateAdd1Year->modify('+1 year');
            $lastDayOfProbation = $hireDateAdd1Year->format('Y-m-d') == date('Y-m-d');
//            $yearsOfService = $hireDate->diff(new \DateTime('now'))->y;
            // allow 6th to 12th month only
            if (!($lastDayOfProbation || $monthsOfService > 5)) {
                continue;
            }

            // on 12th month only, allocate pro-rated annual leaves for this year and exit
            if ($lastDayOfProbation) {
                // check already allocated probation leaves this month
                $balanceLogTypeLocal = $this->balanceLog::TYPE_PRORATED_ANNUAL_LOCAL_LEAVE;
                $balanceLogTypeSick = $this->balanceLog::TYPE_PRORATED_ANNUAL_SICK_LEAVE;
                if ($this->hasReceivedLeaveTypeThisDateFormat($user, [$balanceLogTypeLocal, $balanceLogTypeSick])) {
                    continue;
                }

                $this->addAndSaveAnnualLeaves($user, $balanceLogTypeLocal, $balanceLogTypeSick, $lastDayOfProbation);

                // do not exit if no absence was found, continue process to get last probation leave also
                if ($absenceDate != null) {
                    continue;
                }
            }

            // allow day matching $hireOrResetDate's day only
            if (!$this->isDayPartMatchingToday($hireOrResetDate)) {
                continue;
            }

            // check already allocated probation leaves this month
            $balanceLogTypeLocal = $this->balanceLog::TYPE_PROBATION_LOCAL_LEAVE;
            $balanceLogTypeSick = $this->balanceLog::TYPE_PROBATION_SICK_LEAVE;
            if ($this->hasReceivedLeaveTypeThisDateFormat($user, [$balanceLogTypeLocal, $balanceLogTypeSick])) {
                continue;
            }

            // all conditions satisfied, increment & log

            $oldLocalBalance = $user->getLocalBalance();
            $oldSickBalance = $user->getSickBalance();

            // for 6th and 12th month, adjust amount based on valid date
            $incrementAmount = $this->getProbationLeaveIncrementAmount($monthsOfService, $user, $lastDayOfProbation);
            $user->incrementLocalLeave($incrementAmount);
            $user->incrementSickLeave($incrementAmount);

            $localDescription = sprintf($this->balanceLog::TYPE_PROBATION_LOCAL_LEAVE_DESC, $oldLocalBalance, $user->getLocalBalance());
            $sickDescription = sprintf($this->balanceLog::TYPE_PROBATION_SICK_LEAVE_DESC, $oldSickBalance, $user->getSickBalance());
            $balanceLogLocal = new BalanceLog($localDescription, $user, 'system', $balanceLogTypeLocal);
            $balanceLogLocal->setProbationLeaveAmount($incrementAmount);
            $balanceLogSick = new BalanceLog($sickDescription, $user, 'system', $balanceLogTypeSick);
            $balanceLogSick->setProbationLeaveAmount($incrementAmount);

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
        // run only on end of 31 Dec, time in schedule task
//        if (date('m-d') != '12-31') {
//            return;
//        }
        
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
            $this->addAndSaveAnnualLeaves($user, $balanceLogTypeLocal, $balanceLogTypeSick);
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
        if ($user && $user->getHireDate() instanceOf \DateTime) {
            return true;
        }
        return false;
    }
    
    /**
     * @param $hireDate
     *
     * @return bool
     */
    private function isDayPartMatchingToday($hireDate)
    {
        if (!$hireDate instanceof \DateTime) {
            return false;
        }
        
        // skip not matching condition only if hire date 29 Feb, current year is not leap year and current date is 28 Feb
        $isLeapYear = date('L');
        if ($hireDate->format('m-d') == '02-29' && !$isLeapYear && date('m-d') == '02-28') {
            return true;
        }
        
        if ($hireDate->format('d') == date('d')) {
            return true;
        }
        
        return false;
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
     * @param boolean $isProratedYear
     *
     * @return array
     */
    private function getAnnualLeaveAmountsByUserType($user, $isProratedYear = false)
    {
        $userType = '';
        if ($user->getUserType()) {
            $userType = $user->getUserType();
        }

        switch($userType) {
            // support comex sub group
            case 'Excom':
                $localAmount = 25;
                $sickAmount = 15;
                break;
            // support attendant
            case 'Office attendant':
                $localAmount = 16;
                $sickAmount = 21;
                break;
            default:
                $localAmount = 22;
                $sickAmount = 15;
        }

        if ($isProratedYear) {
            $currentYear = date('Y');
            $endOfYear = new \DateTime(sprintf('%s-12-31 23:59:59', $currentYear));
            $monthsTillEOY = $user->getHireDate()->modify('+1 year')->diff($endOfYear)->m;
            $monthsTillEOY = date('d') <= 15 ? $monthsTillEOY + 1 : $monthsTillEOY;
            $localAmount = ($localAmount / 12) * $monthsTillEOY;
            $sickAmount = ($sickAmount / 12) * $monthsTillEOY;
            // rounded down to nearest 0.5
            $localAmount = floor($localAmount * 2) / 2;
            $sickAmount = floor($sickAmount * 2) / 2;
        }
        
        return [$localAmount, $sickAmount];
    }
    
    /**
     * Returns probation leave increment value based on nth month and date
     *
     * @param $monthsOfService
     * @param $user
     * @param $lastDayOfProbation
     *
     * @return float|int|null
     */
    private function getProbationLeaveIncrementAmount($monthsOfService, $user, $lastDayOfProbation)
    {
        $incrementAmount = 1;

        // if first month 6th month after final date, apply pro-rated
        if ($monthsOfService == 6) {
            $dateDayPart = date('d');
            if ($dateDayPart > 22) {
                $incrementAmount = 0;
            } elseif ($dateDayPart > 15) {
                $incrementAmount = 0.5;
            }
        }
        // if 12 months after hire date, do 6 - total allocated before
        elseif ($lastDayOfProbation) {
            $userBalanceLogs = $user->getBalanceLogs();
            $totalProbationLeaveReceived = 0;
            /** @var BalanceLog $userBalanceLog */
            foreach ($userBalanceLogs as $userBalanceLog) {
                if (
                    $userBalanceLog->getType() == $userBalanceLog::TYPE_PROBATION_LOCAL_LEAVE
                    && $this->isDateWithinAYear($userBalanceLog->getCreatedAt(), $user->getHireDate())
                ) {
                    $totalProbationLeaveReceived += $userBalanceLog->getProbationLeaveAmount();
                }
            }

            // should receive a max of 6 probation leaves, expected values being 0 / 0.5 / 1
            $incrementAmount = 6 - $totalProbationLeaveReceived;
            if ($incrementAmount > 1) {
                $incrementAmount = 1;
            }
        }

        return $incrementAmount;
    }

    /**
     * @param $user
     * @param $balanceLogTypeLocal
     * @param $balanceLogTypeSick
     * @param boolean $isProratedFirstYear
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function addAndSaveAnnualLeaves($user, $balanceLogTypeLocal, $balanceLogTypeSick, $isProratedFirstYear = false)
    {
        $oldLocalBalance = $user->getLocalBalance();
        $oldSickBalance = $user->getSickBalance();
        list($localAmount, $sickAmount) = $this->getAnnualLeaveAmountsByUserType($user, $isProratedFirstYear);

        $user->incrementLocalLeave($localAmount);
        if (!$isProratedFirstYear) {
            // max of 90 sick at all times
            if ($oldSickBalance != 90) {
                $user->incrementSickLeave($sickAmount);
            }
            if ($user->getSickBalance() > 90) {
                $user->setSickBalance(90);
            }
        }
        else {
            // no need to check for maximum sick leave reached in first year
            $user->incrementSickLeave($sickAmount);
        }

        $localDescription = sprintf(!$isProratedFirstYear ? $this->balanceLog::TYPE_ANNUAL_LOCAL_LEAVE_DESC : $this->balanceLog::TYPE_PRORATED_ANNUAL_LOCAL_LEAVE_DESC, $oldLocalBalance, $user->getLocalBalance());
        $sickDescription = sprintf(!$isProratedFirstYear ? $this->balanceLog::TYPE_ANNUAL_SICK_LEAVE_DESC : $this->balanceLog::TYPE_PRORATED_ANNUAL_SICK_LEAVE_DESC, $oldSickBalance, $user->getSickBalance());
        $balanceLogLocal = new BalanceLog($localDescription, $user, 'system', $balanceLogTypeLocal);
        $balanceLogSick = new BalanceLog($sickDescription, $user, 'system', $balanceLogTypeSick);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->persist($balanceLogLocal);
        $this->getEntityManager()->persist($balanceLogSick);
        $this->getEntityManager()->flush();
    }

    /**
     * @param $dateToCheck
     * @param $baseLineDate
     * @return bool
     */
    private function isDateWithinAYear($dateToCheck, $baseLineDate)
    {
        $baseLineDateClone = clone $baseLineDate;
        $baseLineDateAdd1Year = $baseLineDateClone->modify('+1 year');
        return $dateToCheck >= $baseLineDate && $dateToCheck <= $baseLineDateAdd1Year;
    }
}
