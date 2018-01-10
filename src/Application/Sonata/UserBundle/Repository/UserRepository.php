<?php

namespace Application\Sonata\UserBundle\Repository;

/**
 * UserRepository
 *
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Returns users sorted by lastname ASC, and removes logged in user if $user is passed
     * @param \Application\Sonata\UserBundle\Entity\User $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUsersQueryBuilder($user = null) {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder->orderBy('u.lastname', 'ASC')
            ->orderBy('u.firstname', 'ASC');
        if ($user) {
            $queryBuilder->where('u.lastname != :lastname')
                ->andWhere('u.firstname != :firstname')
                ->setParameter('lastname', $user->getLastname())
                ->setParameter('firstname', $user->getFirstname());
        }
        return $queryBuilder;
    }
}
