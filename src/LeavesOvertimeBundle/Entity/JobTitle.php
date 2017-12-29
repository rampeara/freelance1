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
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true, unique=false)
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
     * @param string|null $name
     *
     * @return JobTitle
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }
}
