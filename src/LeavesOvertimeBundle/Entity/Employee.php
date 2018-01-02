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
    // ManyToOne
    
    /**
     * @ORM\ManyToOne(targetEntity="JobTitle", inversedBy="employees")
     * @ORM\JoinColumn(name="job_title_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $jobTitle;
    
    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="employees")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $department;
    
    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="employees")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $project;
    
    /**
     * @ORM\ManyToOne(targetEntity="BusinessUnit", inversedBy="employees")
     * @ORM\JoinColumn(name="business_unit_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $businessUnit;
    
    /**
     * @ORM\OneToMany(targetEntity="Employee", mappedBy="employee")
     */
    private $leaves;
    
    // ManyToMany
    
    /**
     * @ORM\ManyToMany(targetEntity="Employee")
     * @ORM\JoinTable(name="axa_supervisors_level1",
     *      joinColumns={@ORM\JoinColumn(name="employee_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="supervisor_level1_id", referencedColumnName="id")}
     *      )
     */
    private $supervisorsLevel1;
    
    /**
     * @ORM\ManyToMany(targetEntity="Employee")
     * @ORM\JoinTable(name="axa_supervisors_level2",
     *      joinColumns={@ORM\JoinColumn(name="employee_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="supervisor_level2_id", referencedColumnName="id")}
     *      )
     */
    private $supervisorsLevel2;
    
    public function __construct()
    {
        $this->supervisorsLevel1 = new ArrayCollection();
        $this->supervisorsLevel2 = new ArrayCollection();
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
     * @ORM\Column(name="gender", type="string", length=255, nullable=true, unique=false)
     */
    private $gender;

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
     * Set gender.
     *
     * @param string|null $gender
     *
     * @return Employee
     */
    public function setGender($gender = null)
    {
        $this->gender = $gender;
        
        return $this;
    }
    
    /**
     * Get gender.
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->gender;
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
    public function getDepartment() {
        return $this->department;
    }
    
    /**
     * @param mixed $department
     */
    public function setDepartment($department) {
        $this->department = $department;
    }
    
    /**
     * @return mixed
     */
    public function getProject() {
        return $this->project;
    }
    
    /**
     * @param mixed $project
     */
    public function setProject($project) {
        $this->project = $project;
    }
    
    /**
     * @return mixed
     */
    public function getBusinessUnit() {
        return $this->businessUnit;
    }
    
    /**
     * @param mixed $businessUnit
     */
    public function setBusinessUnit($businessUnit) {
        $this->businessUnit = $businessUnit;
    }
    
    

    /**
     * Add supervisorsLevel1.
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $supervisorsLevel1
     *
     * @return Employee
     */
    public function addSupervisorsLevel1(\LeavesOvertimeBundle\Entity\Employee $supervisorsLevel1)
    {
        $this->supervisorsLevel1[] = $supervisorsLevel1;

        return $this;
    }

    /**
     * Remove supervisorsLevel1.
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $supervisorsLevel1
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeSupervisorsLevel1(\LeavesOvertimeBundle\Entity\Employee $supervisorsLevel1)
    {
        return $this->supervisorsLevel1->removeElement($supervisorsLevel1);
    }

    /**
     * Get supervisorsLevel1.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupervisorsLevel1()
    {
        return $this->supervisorsLevel1;
    }

    /**
     * Add supervisorsLevel2.
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $supervisorsLevel2
     *
     * @return Employee
     */
    public function addSupervisorsLevel2(\LeavesOvertimeBundle\Entity\Employee $supervisorsLevel2)
    {
        $this->supervisorsLevel2[] = $supervisorsLevel2;

        return $this;
    }

    /**
     * Remove supervisorsLevel2.
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $supervisorsLevel2
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeSupervisorsLevel2(\LeavesOvertimeBundle\Entity\Employee $supervisorsLevel2)
    {
        return $this->supervisorsLevel2->removeElement($supervisorsLevel2);
    }

    /**
     * Get supervisorsLevel2.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSupervisorsLevel2()
    {
        return $this->supervisorsLevel2;
    }
    
    /**
     * Diff in years since hire date till departure date or now
     * @return int
     */
    public function getYearsOfService() {
        if (!empty($this->hireDate) && !empty($this->departureDate)) {
            return $this->departureDate->diff($this->hireDate)->y;
        }
        return $this->hireDate->diff(new \DateTime('now'))->y;
    }
    
    public function getSupervisorsLevel1String() {
        return $this->getSupervisorsString($this->supervisorsLevel1);
    }
    
    public function getSupervisorsLevel2String() {
        return $this->getSupervisorsString($this->supervisorsLevel2);
    }
    
    /**
     * Converts array collection of supervisors into comma separated string
     * @param $supervisorsArray ArrayCollection
     * @return string
     */
    protected function getSupervisorsString($supervisorsArray) {
        $supervisors = [];
        /* @var $supervisor \LeavesOvertimeBundle\Entity\Employee */
        foreach ($supervisorsArray as $supervisor) {
            $supervisors[] = $supervisor->getFullName();
        }
        
        return join(', ', $supervisors);
    }

    /**
     * Add leave
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $leave
     *
     * @return Employee
     */
    public function addLeave(\LeavesOvertimeBundle\Entity\Employee $leave)
    {
        $this->leaves[] = $leave;

        return $this;
    }

    /**
     * Remove leave
     *
     * @param \LeavesOvertimeBundle\Entity\Employee $leave
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLeave(\LeavesOvertimeBundle\Entity\Employee $leave)
    {
        return $this->leaves->removeElement($leave);
    }

    /**
     * Get leaves.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeaves()
    {
        return $this->leaves;
    }
}
