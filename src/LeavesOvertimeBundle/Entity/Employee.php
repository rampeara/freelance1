<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="business_unit", type="string", length=255, nullable=true, unique=false)
     */
    private $businessUnit;

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
     * @var string|null
     *
     * @ORM\Column(name="approver_n1", type="string", length=255, nullable=true, unique=false)
     */
    private $approverN1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="approver_n2", type="string", length=255, nullable=true, unique=false)
     */
    private $approverN2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="approver_n3", type="string", length=255, nullable=true, unique=false)
     */
    private $approverN3;

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
     * Set approverN1.
     *
     * @param string|null $approverN1
     *
     * @return Employee
     */
    public function setApproverN1($approverN1 = null)
    {
        $this->approverN1 = $approverN1;

        return $this;
    }

    /**
     * Get approverN1.
     *
     * @return string|null
     */
    public function getApproverN1()
    {
        return $this->approverN1;
    }

    /**
     * Set approverN2.
     *
     * @param string|null $approverN2
     *
     * @return Employee
     */
    public function setApproverN2($approverN2 = null)
    {
        $this->approverN2 = $approverN2;

        return $this;
    }

    /**
     * Get approverN2.
     *
     * @return string|null
     */
    public function getApproverN2()
    {
        return $this->approverN2;
    }

    /**
     * Set approverN3.
     *
     * @param string|null $approverN3
     *
     * @return Employee
     */
    public function setApproverN3($approverN3 = null)
    {
        $this->approverN3 = $approverN3;

        return $this;
    }

    /**
     * Get approverN3.
     *
     * @return string|null
     */
    public function getApproverN3()
    {
        return $this->approverN3;
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
}
