<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BalanceLog
 *
 * @ORM\Table(name="axa_balance_log")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\BalanceLogRepository")
 */
class BalanceLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var \Application\Sonata\UserBundle\Entity\User|null
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User", inversedBy="balanceLogs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var float|null
     *
     * @ORM\Column(name="previousBalance", type="float", nullable=true)
     */
    private $previousBalance;

    /**
     * @var float|null
     *
     * @ORM\Column(name="newBalance", type="float", nullable=true)
     */
    private $newBalance;
    
    /**
     * @var string|null
     *
     * @ORM\Column(name="action", type="string", length=255, nullable=true)
     */
    private $action;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="created_by", type="string", length=255, nullable=true)
     */
    private $createdBy;
    
    public function __construct($user = null, $type = null, $previousBalance = null, $newBalance = null, $createdBy = null, $action = null)
    {
        $this->user = $user;
        $this->type = $type;
        $this->previousBalance = $previousBalance;
        $this->newBalance = $newBalance;
        $this->createdAt = new \DateTime();
        $this->createdBy = $createdBy;
        $this->action = $action;
    }
    
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
     * @return BalanceLog
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
     * Set previousBalance.
     *
     * @param float|null $previousBalance
     *
     * @return BalanceLog
     */
    public function setPreviousBalance($previousBalance = null)
    {
        $this->previousBalance = $previousBalance;

        return $this;
    }

    /**
     * Get previousBalance.
     *
     * @return float|null
     */
    public function getPreviousBalance()
    {
        return $this->previousBalance;
    }

    /**
     * Set newBalance.
     *
     * @param float|null $newBalance
     *
     * @return BalanceLog
     */
    public function setNewBalance($newBalance = null)
    {
        $this->newBalance = $newBalance;

        return $this;
    }

    /**
     * Get newBalance.
     *
     * @return float|null
     */
    public function getNewBalance()
    {
        return $this->newBalance;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime|null $createdAt
     *
     * @return BalanceLog
     */
    public function setCreatedAt($createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy.
     *
     * @param string|null $createdBy
     *
     * @return BalanceLog
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
     * @param \Application\Sonata\UserBundle\Entity\User|null $user
     *
     * @return BalanceLog
     */
    public function setUser(?\Application\Sonata\UserBundle\Entity\User $user): BalanceLog
    {
        $this->user = $user;
        return $this;
    }
    
    /**
     * @return \Application\Sonata\UserBundle\Entity\User|null
     */
    public function getUser(): ?\Application\Sonata\UserBundle\Entity\User
    {
        return $this->user;
    }
    
    /**
     * @param null|string $action
     *
     * @return BalanceLog
     */
    public function setAction(?string $action): BalanceLog
    {
        $this->action = $action;
        return $this;
}
    
    /**
     * @return null|string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }
}
