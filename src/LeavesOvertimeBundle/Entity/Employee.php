<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Employee
 *
 * @ORM\Table(name="axa_employee")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\EmployeeRepository")
 */
class Employee extends EntityBase
{
    /**
     * @ORM\ManyToOne(targetEntity="JobTitle", inversedBy="employees")
     * @ORM\JoinColumn(name="job_title_id", referencedColumnName="id")
     */
    private $jobTitle;
    
    /**
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="supervisors")
     * @ORM\JoinColumn(name="supervisor1_id", referencedColumnName="id")
     */
    private $supervisor1;
    
    /**
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="supervisors")
     * @ORM\JoinColumn(name="supervisor2_id", referencedColumnName="id")
     */
    private $supervisor2;
    
    /**
     * @ORM\ManyToOne(targetEntity="Employee", inversedBy="supervisors")
     * @ORM\JoinColumn(name="supervisor3_id", referencedColumnName="id")
     */
    private $supervisor3;
    
    /**
     * @ORM\ManyToOne(targetEntity="BusinessUnit", inversedBy="employees")
     * @ORM\JoinColumn(name="business_unit_id", referencedColumnName="id")
     */
    private $businessUnit;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="supervisor1")
     */
    private $supervisors;
    
    public function __construct()
    {
        $this->supervisors = new ArrayCollection();
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
     * @ORM\Column(name="ab_number", type="string", length=255, nullable=true, unique=false)
     */
    private $abNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true, unique=false)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false, unique=false)
     */
    private $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false, unique=false)
     */
    private $lastName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false, unique=false)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="department", type="string", length=255, nullable=true, unique=false)
     */
    private $department;

    /**
     * @var string|null
     *
     * @ORM\Column(name="project", type="string", length=255, nullable=true, unique=false)
     */
    private $project;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="hire_date", type="date", nullable=false)
     */
    private $hireDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="employment_status", type="string", length=255, nullable=true, unique=false)
     */
    private $employmentStatus;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="departure_date", type="date", nullable=true)
     */
    private $departureDate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="departure_reason", type="string", length=255, nullable=true, unique=false)
     */
    private $departureReason;


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
     * Set abNumber.
     *
     * @param string|null $abNumber
     *
     * @return Employee
     */
    public function setAbNumber($abNumber = null)
    {
        $this->abNumber = $abNumber;

        return $this;
    }

    /**
     * Get abNumber.
     *
     * @return string|null
     */
    public function getAbNumber()
    {
        return $this->abNumber;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return Employee
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set firstName.
     *
     * @param string|null $firstName
     *
     * @return Employee
     */
    public function setFirstName($firstName = null)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName.
     *
     * @param string|null $lastName
     *
     * @return Employee
     */
    public function setLastName($lastName = null)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set email.
     *
     * @param string|null $email
     *
     * @return Employee
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set businessUnit.
     *
     * @param string|null $businessUnit
     *
     * @return Employee
     */
    public function setBusinessUnit($businessUnit = null)
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }

    /**
     * Get businessUnit.
     *
     * @return string|null
     */
    public function getBusinessUnit()
    {
        return $this->businessUnit;
    }

    /**
     * Set department.
     *
     * @param string|null $department
     *
     * @return Employee
     */
    public function setDepartment($department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department.
     *
     * @return string|null
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set project.
     *
     * @param string|null $project
     *
     * @return Employee
     */
    public function setProject($project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project.
     *
     * @return string|null
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set hireDate.
     *
     * @param \DateTime|null $hireDate
     *
     * @return Employee
     */
    public function setHireDate($hireDate = null)
    {
        $this->hireDate = $hireDate;

        return $this;
    }

    /**
     * Get hireDate.
     *
     * @return \DateTime|null
     */
    public function getHireDate()
    {
        return $this->hireDate;
    }

    /**
     * Set employmentStatus.
     *
     * @param string|null $employmentStatus
     *
     * @return Employee
     */
    public function setEmploymentStatus($employmentStatus = null)
    {
        $this->employmentStatus = $employmentStatus;

        return $this;
    }

    /**
     * Get employmentStatus.
     *
     * @return string|null
     */
    public function getEmploymentStatus()
    {
        return $this->employmentStatus;
    }

    /**
     * Set departureDate.
     *
     * @param \DateTime|null $departureDate
     *
     * @return Employee
     */
    public function setDepartureDate($departureDate = null)
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    /**
     * Get departureDate.
     *
     * @return \DateTime|null
     */
    public function getDepartureDate()
    {
        return $this->departureDate;
    }

    /**
     * Set departureReason.
     *
     * @param string|null $departureReason
     *
     * @return Employee
     */
    public function setDepartureReason($departureReason = null)
    {
        $this->departureReason = $departureReason;

        return $this;
    }

    /**
     * Get departureReason.
     *
     * @return string|null
     */
    public function getDepartureReason()
    {
        return $this->departureReason;
    }
  
    /**
     * @return mixed
     */
    public function getJobTitle() {
      return $this->jobTitle;
    }
    
    /**
     * @param mixed $jobTitle
     */
    public function setJobTitle($jobTitle) {
      $this->jobTitle = $jobTitle;
    }
    
    /**
     * @return string
     */
    public function getFullName() {
        return sprintf("%s %s", $this->lastName, $this->firstName);
    }
    
    /**
     * @return mixed
     */
    public function getSupervisor1() {
        return $this->supervisor1;
    }
    
    /**
     * @param mixed $supervisor1
     */
    public function setSupervisor1($supervisor1) {
        $this->supervisor1 = $supervisor1;
    }
    
    /**
     * @return mixed
     */
    public function getSupervisor2() {
        return $this->supervisor2;
    }
    
    /**
     * @param mixed $supervisor2
     */
    public function setSupervisor2($supervisor2) {
        $this->supervisor2 = $supervisor2;
    }
    
    /**
     * @return mixed
     */
    public function getSupervisor3() {
        return $this->supervisor3;
    }
    
    /**
     * @param mixed $supervisor3
     */
    public function setSupervisor3($supervisor3) {
        $this->supervisor3 = $supervisor3;
    }
}
