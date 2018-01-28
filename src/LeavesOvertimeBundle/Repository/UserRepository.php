<?php

namespace LeavesOvertimeBundle\Repository;

use LeavesOvertimeBundle\Entity\BalanceLog;
use LeavesOvertimeBundle\Entity\Leaves;

class UserRepository extends \Doctrine\ORM\EntityRepository
{
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
    
    public function incrementBalancesForProbation()
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
        $date = new \DateTime('-1 year');
        $date = $date->format('Y-m-d');
        $usersLessThanAYear = $this->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from('ApplicationSonataUserBundle:User', 'u')
            ->where(':date <= u.hireDate')
            ->andWhere('u.isNoProbationLeaves != 1')
            ->orderBy('u.hireDate', 'ASC')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult()
        ;
        
        if (empty($usersLessThanAYear)) {
            return;
        }
        
        /** @var \Application\Sonata\UserBundle\Entity\User $user */
        foreach ($usersLessThanAYear as $user) {
            if (!$user && !$user->getHireDate() && empty($user->getHireDate())) {
                continue;
            }

            $hireDate = $user->getHireDate();
            $monthsOfService = $hireDate->diff(new \DateTime('now'))->m;
            $yearsOfService = $hireDate->diff(new \DateTime('now'))->y;
            if (!($monthsOfService > 6 || $yearsOfService == 1)) {
                continue;
            }
            
            if ($hireDate->format('d') != date('d')) {
                if (!($hireDate->format('m-d') == '02-29' && date('m-d') == '02-28')) {
                    continue;
                }
            }
            
            $userLeaves = $user->getLeaves();
            $noProbabtionLeaves = false;
            /** @var Leaves $userLeave */
            foreach ($userLeaves as $userLeave) {
                if (in_array($userLeave->getType(), $invalidLeaveType)) {
                    $noProbabtionLeaves = true;
                    break;
                }
            }
            if ($noProbabtionLeaves) {
                // user will only get leaves after 1 year completion
                // set no probation leaves to true so that next time the search result doesn't take this user into account
                $user->setIsNoProbationLeaves($noProbabtionLeaves);
                $this->getEntityManager()->persist($user);
                $this->getEntityManager()->flush();
                continue;
            }
            
            // received leaves for this month?
            $userBalanceLogs = $user->getBalanceLogs();
            $probationLeaveReceived = false;
            $balanceLog = new BalanceLog();
            /** @var BalanceLog $userBalanceLog */
            foreach ($userBalanceLogs as $userBalanceLog) {
                $logDate = $userBalanceLog->getCreatedAt()->format('Y-m');
                $currentDate = new \DateTime('now');
                $currentDateFormatted = $currentDate->format('Y-m');
                if (
                    ($userBalanceLog->getType() == $balanceLog::TYPE_PROBATION_LOCAL_LEAVE
                    || $userBalanceLog->getType() == $balanceLog::TYPE_PROBATION_SICK_LEAVE)
                    && $logDate == $currentDateFormatted) {
                    $probationLeaveReceived = true;
                    break;
                }
            }
            
            if ($probationLeaveReceived) {
                continue;
            }
            
            // all conditions satisfied, increment & log
            $oldLocalBalance = $user->getLocalBalance();
            $oldSickBalance = $user->getSickBalance();
            $user->incrementLocalLeave();
            $user->incrementSickLeave();
            $balanceLogLocal = new BalanceLog($oldLocalBalance, $user->getLocalBalance(), $user, 'system', $balanceLog::TYPE_PROBATION_LOCAL_LEAVE);
            $balanceLogSick = new BalanceLog($oldSickBalance, $user->getSickBalance(), $user, 'system', $balanceLog::TYPE_PROBATION_SICK_LEAVE);
            
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->persist($balanceLogLocal);
            $this->getEntityManager()->persist($balanceLogSick);
            $this->getEntityManager()->flush();
        }
    }
}
