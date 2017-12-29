<?php

namespace LeavesOvertimeBundle\Repository;

class CommonRepository extends \Doctrine\ORM\EntityRepository
{
    
    /**
     * Finds employees referenced with passed entity
     * @param string $entityId
     * @param string $entityName
     *
     * @return array
     */
    public function findReferencedEmployees($entityId, $entityName) {
        $entityName = 'e.' . $entityName;
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('e.id, e.lastName, e.firstName')
            ->from('LeavesOvertimeBundle:Employee', 'e')
            ->join($entityName, 'je')
            ->where('je.id = :id')
            ->orderBy('e.lastName, e.firstName', 'ASC')
            ->setParameter('id', $entityId)
            ->getQuery()
        ;
        
        return $query->getResult();
    }
}
