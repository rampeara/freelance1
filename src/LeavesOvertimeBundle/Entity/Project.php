<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Project
 *
 * @ORM\Table(name="axa_project")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\ProjectRepository")
 */
class Project extends SimpleEntity
{
    /**
     * @ORM\OneToMany(targetEntity="Application\Sonata\UserBundle\Entity\User", mappedBy="project")
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
     * @return Project
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
