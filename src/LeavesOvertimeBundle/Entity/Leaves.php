<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Leaves
 *
 * @ORM\Table(name="axa_leaves")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\LeavesRepository")
 */
class Leaves extends EntityBase
{
    /**
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="leaves")
     * @ORM\JoinColumn(name="employee_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $employee;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    private $endDate;


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
     * Set type.
     *
     * @param string|null $type
     *
     * @return Leaves
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime|null $startDate
     *
     * @return Leaves
     */
    public function setStartDate($startDate = null)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime|null $endDate
     *
     * @return Leaves
     */
    public function setEndDate($endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set employee.
     *
     * @param \LeavesOvertimeBundle\Entity\Employee|null $employee
     *
     * @return Leaves
     */
    public function setEmployee(\LeavesOvertimeBundle\Entity\Employee $employee = null)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get employee.
     *
     * @return \LeavesOvertimeBundle\Entity\Employee|null
     */
    public function getEmployee()
    {
        return $this->employee;
    }
}
