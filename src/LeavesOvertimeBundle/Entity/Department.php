<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Department
 *
 * @ORM\Table(name="axa_department")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\DepartmentRepository")
 */
class Department extends SimpleEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Application\Sonata\UserBundle\Entity\User", mappedBy="department")
     */
    protected $users;
    
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }
    
    /**
     * Add user.
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     *
     * @return Department
     */
    public function addUser(\Application\Sonata\UserBundle\Entity\User $user)
    {
        $this->users[] = $user;
        
        return $this;
    }
    
    /**
     * Remove user.
     *
     * @param \Application\Sonata\UserBundle\Entity\User $user
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUser(\Application\Sonata\UserBundle\Entity\User $user)
    {
        return $this->users->removeElement($user);
    }
    
    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }
}
