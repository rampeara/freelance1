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
class JobTitle extends EntityBase
{
    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="jobTitle")
     */
    private $employees;
    
    public function __construct()
    {
      $this->employees = new ArrayCollection();
    }
  
    public function __toString() {
        return $this->value;
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
     * @var string|null
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true, unique=false)
     */
    private $value;


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
     * Set value.
     *
     * @param string|null $value
     *
     * @return JobTitle
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
