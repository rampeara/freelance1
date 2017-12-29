<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Department
 *
 * @ORM\Table(name="axa_department")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\DepartmentRepository")
 */
class Department extends SimpleEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="department")
     */
    private $employees;
    
    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }
    
    /**
     * Add employee.
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $employee
     *
     * @return Department
     */
    public function addEmployee(\LeavesOvertimeBundle\Entity\Employee $employee)
    {
        $this->employees[] = $employee;
        
        return $this;
    }
    
    /**
     * Remove employee.
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $employee
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeEmployee(\LeavesOvertimeBundle\Entity\Employee $employee)
    {
        return $this->employees->removeElement($employee);
    }
    
    /**
     * Get employees.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployees()
    {
        return $this->employees;
    }
}
