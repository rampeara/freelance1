<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * JobTitle
 *
 * @ORM\Table(name="axa_job_title")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\JobTitleRepository")
 */
class JobTitle extends SimpleEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="jobTitle")
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
     * @return JobTitle
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
