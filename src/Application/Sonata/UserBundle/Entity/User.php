<?php

/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\UserBundle\Entity;

use Sonata\UserBundle\Entity\BaseUser as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This file has been generated by the Sonata EasyExtends bundle.
 *
 * @link https://sonata-project.org/bundles/easy-extends
 *
 * References :
 *   working with object : http://www.doctrine-project.org/projects/orm/2.0/docs/reference/working-with-objects/en
 *
 * @author <yourname> <youremail>
 */
class User extends BaseUser
{
    
    protected $jobTitle;
    
    protected $department;
    
    protected $project;
    
    protected $businessUnit;
    
    protected $leaves;
    
    protected $supervisorsLevel1;
    
    protected $supervisorsLevel2;
    
    /**
     * @var int $id
     */
    protected $id;
    
    /**
     * @var string|null
     */
    protected $abNumber;
    
    /**
     * @var string|null
     */
    protected $title;
    
    /**
     * @var \DateTime|null
     */
    protected $hireDate;
    
    /**
     * @var string|null
     */
    protected $employmentStatus;
    
    /**
     * @var \DateTime|null
     */
    protected $departureDate;
    
    /**
     * @var string|null
     */
    protected $departureReason;
    
    /**
     * @var string|null
     */
    protected $createdBy;
    
    /**
     * @var string|null
     */
    protected $updatedBy;
    
    /**
     * Returns a string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFullname() ?: '-';
    }
    
    /**
     * Diff in years since hire date till departure date or now
     * @return int
     */
    public function getYearsOfService() {
        if (!empty($this->hireDate) && !empty($this->departureDate)) {
            if (empty($this->departureDate)) {
                return $this->hireDate->diff(new \DateTime('now'))->y;
            }
            return $this->departureDate->diff($this->hireDate)->y;
        }
        return 0;
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
        /* @var $supervisor \Application\Sonata\UserBundle\Entity\User */
        foreach ($supervisorsArray as $supervisor) {
            $supervisors[] = $supervisor->getFullname();
        }
        
        return join(', ', $supervisors);
    }
    
    /**
     * Get id
     *
     * @return int $id
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
     * @return User
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
     * @return User
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
     * Set hireDate.
     *
     * @param \DateTime $hireDate
     *
     * @return User
     */
    public function setHireDate($hireDate)
    {
        $this->hireDate = $hireDate;

        return $this;
    }

    /**
     * Get hireDate.
     *
     * @return \DateTime
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
     * @return User
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
     * @return User
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
     * @return User
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
     * Set createdBy.
     *
     * @param string|null $createdBy
     *
     * @return User
     */
    public function setCreatedBy($createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return string|null
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy.
     *
     * @param string|null $updatedBy
     *
     * @return User
     */
    public function setUpdatedBy($updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy.
     *
     * @return string|null
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->supervisorsLevel1 = new ArrayCollection();
        $this->supervisorsLevel2 = new ArrayCollection();
    }

    /**
     * Add leave.
     *
     * @param \LeavesOvertimeBundle\Entity\Leaves $leave
     *
     * @return User
     */
    public function addLeave(\LeavesOvertimeBundle\Entity\Leaves $leave)
    {
        $this->leaves[] = $leave;

        return $this;
    }

    /**
     * Remove leave.
     *
     * @param \LeavesOvertimeBundle\Entity\Leaves $leave
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLeave(\LeavesOvertimeBundle\Entity\Leaves $leave)
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

    /**
     * Set jobTitle.
     *
     * @param \LeavesOvertimeBundle\Entity\JobTitle|null $jobTitle
     *
     * @return User
     */
    public function setJobTitle(\LeavesOvertimeBundle\Entity\JobTitle $jobTitle = null)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * Get jobTitle.
     *
     * @return \LeavesOvertimeBundle\Entity\JobTitle|null
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * Set department.
     *
     * @param \LeavesOvertimeBundle\Entity\Department|null $department
     *
     * @return User
     */
    public function setDepartment(\LeavesOvertimeBundle\Entity\Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department.
     *
     * @return \LeavesOvertimeBundle\Entity\Department|null
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set project.
     *
     * @param \LeavesOvertimeBundle\Entity\Project|null $project
     *
     * @return User
     */
    public function setProject(\LeavesOvertimeBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project.
     *
     * @return \LeavesOvertimeBundle\Entity\Project|null
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set businessUnit.
     *
     * @param \LeavesOvertimeBundle\Entity\BusinessUnit|null $businessUnit
     *
     * @return User
     */
    public function setBusinessUnit(\LeavesOvertimeBundle\Entity\BusinessUnit $businessUnit = null)
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }

    /**
     * Get businessUnit.
     *
     * @return \LeavesOvertimeBundle\Entity\BusinessUnit|null
     */
    public function getBusinessUnit()
    {
        return $this->businessUnit;
    }

    /**
     * Add supervisor Level1.
     *
     * @param \Application\Sonata\UserBundle\Entity\User $supervisorLevel1
     *
     * @return User
     */
    public function addSupervisorLevel1(\Application\Sonata\UserBundle\Entity\User $supervisorLevel1)
    {
        $this->supervisorsLevel1[] = $supervisorLevel1;

        return $this;
    }

    /**
     * Remove supervisor Level1.
     *
     * @param \Application\Sonata\UserBundle\Entity\User $supervisorLevel1
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeSupervisorLevel1(\Application\Sonata\UserBundle\Entity\User $supervisorLevel1)
    {
        return $this->supervisorsLevel1->removeElement($supervisorLevel1);
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
     * Add supervisor Level2.
     *
     * @param \Application\Sonata\UserBundle\Entity\User $supervisorLevel2
     *
     * @return User
     */
    public function addSupervisorsLevel2(\Application\Sonata\UserBundle\Entity\User $supervisorLevel2)
    {
        $this->supervisorsLevel2[] = $supervisorLevel2;

        return $this;
    }

    /**
     * Remove supervisor Level2.
     *
     * @param \Application\Sonata\UserBundle\Entity\User $supervisorLevel2
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeSupervisorsLevel2(\Application\Sonata\UserBundle\Entity\User $supervisorLevel2)
    {
        return $this->supervisorsLevel2->removeElement($supervisorLevel2);
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
     * @param \Doctrine\Common\Collections\ArrayCollection $supervisorsLevel1
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function setSupervisorsLevel1($supervisorsLevel1) {
        $this->supervisorsLevel1 = $supervisorsLevel1;
    
        return $this;
    }
    
    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $supervisorsLevel2
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function setSupervisorsLevel2($supervisorsLevel2) {
        $this->supervisorsLevel2 = $supervisorsLevel2;
    
        return $this;
    }
    
}
