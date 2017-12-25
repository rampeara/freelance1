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
        return $this->name;
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
}
