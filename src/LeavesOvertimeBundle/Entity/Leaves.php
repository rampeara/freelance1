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
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User", inversedBy="leaves")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $user;
    
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
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

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
     * Set user.
     *
     * @param \Application\Sonata\UserBundle\Entity\User|null $user
     *
     * @return Leaves
     */
    public function setUser(\Application\Sonata\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Application\Sonata\UserBundle\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * @return null|string
     */
    public function getStatus() {
        return $this->status;
    }
    
    /**
     * @param null|string $status
     *
     * @return \LeavesOvertimeBundle\Entity\Leaves
     */
    public function setStatus($status) {
        $this->status = $status;
        
        return $this;
    }
}
