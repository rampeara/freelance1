<?php

namespace LeavesOvertimeBundle\Repository;

class CommonRepository extends \Doctrine\ORM\EntityRepository
{
    
    /**
     * Finds users referenced with passed entity
     * @param string $entityId
     * @param string $entityName
     *
     * @return array
     */
    public function findReferencedUsers($entityId, $entityName) {
        $entityName = 'u.' . $entityName;
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('u.id, u.lastname, u.firstname')
            ->from('Application\Sonata\UserBundle\Entity\User', 'u')
            ->join($entityName, 'je')
            ->where('je.id = :id')
            ->orderBy('u.lastname, u.firstname', 'ASC')
            ->setParameter('id', $entityId)
            ->getQuery()
        ;
        
        return $query->getResult();
    }
}
