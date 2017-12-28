<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * BusinessUnit
 *
 * @ORM\Table(name="axa_business_unit")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\BusinessUnitRepository")
 */
class BusinessUnit extends EntityBase
{
    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="businessUnit")
     */
    private $employees;
    
    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }
    
    public function __toString() {
        return !empty($this->name) ? $this->name : '';
    }
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false, unique=false)
     */
    private $name;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return BusinessUnit
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add employee.
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $employee
     *
     * @return BusinessUnit
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
