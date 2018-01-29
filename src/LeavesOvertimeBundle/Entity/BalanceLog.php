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
    const TYPE_PROBATION_LOCAL_LEAVE = 'Probation local leave';
    const TYPE_PROBATION_SICK_LEAVE = 'Probation sick leave';
    const TYPE_ANNUAL_LOCAL_LEAVE = 'Annual local leave';
    const TYPE_ANNUAL_SICK_LEAVE = 'Annual sick leave';
    const TYPE_APPLIED_LEAVE = 'Applied leave';
    const TYPE_CARRY_FORWARD_LOCAL_BALANCE = 'Carry forward local balance';
    const TYPE_FREEZE_CARRY_FORWARD_LOCAL_BALANCE = 'Freeze carry forward local balance';
    const TYPE_FREEZE_LOCAL_BALANCE = 'Freeze local balance';
    
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
    protected $user;
    
    /**
     * @var \LeavesOvertimeBundle\Entity\Leaves|null
     *
     * @ORM\ManyToOne(targetEntity="LeavesOvertimeBundle\Entity\Leaves", inversedBy="balanceLogs")
     * @ORM\JoinColumn(name="leaves_id", referencedColumnName="id")
     */
    protected $leave;

    /**
     * @var float|null
     *
     * @ORM\Column(name="previous_balance", type="float", nullable=true)
     */
    private $previousBalance;

    /**
     * @var float|null
     *
     * @ORM\Column(name="new_balance", type="float", nullable=true)
     */
    private $newBalance;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=true)
     */
    private $type;

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
    
    public function __construct($previousBalance = null, $newBalance = null, $user = null, $createdBy = 'system', $type = self::TYPE_APPLIED_LEAVE, $leave = null)
    {
        $this->leave = $leave;
        $this->user = $user;
        $this->previousBalance = $previousBalance;
        $this->newBalance = $newBalance;
        $this->createdAt = new \DateTime();
        $this->createdBy = $createdBy;
        $this->type = $type == null ? self::TYPE_APPLIED_LEAVE : $type;
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
     * Set leave.
     *
     * @param \LeavesOvertimeBundle\Entity\Leaves|null $leave
     *
     * @return BalanceLog
     */
    public function setLeave(\LeavesOvertimeBundle\Entity\Leaves $leave = null)
    {
        $this->leave = $leave;

        return $this;
    }

    /**
     * Get leave.
     *
     * @return \LeavesOvertimeBundle\Entity\Leaves|null
     */
    public function getLeave()
    {
        return $this->leave;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return BalanceLog
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set user.
     *
     * @param \Application\Sonata\UserBundle\Entity\User|null $user
     *
     * @return BalanceLog
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
}
