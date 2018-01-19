<?php

namespace LeavesOvertimeBundle\Repository;

class UserRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Returns users sorted by lastname ASC, and removes logged in user if $user is passed
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
     * Returns unique ids of given user and their subordinates as a supervisor lvl 1 AND 2
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
}
