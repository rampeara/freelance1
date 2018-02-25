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
    const TYPE_PROBATION_LOCAL_LEAVE = 1;
    const TYPE_PROBATION_SICK_LEAVE = 2;
    const TYPE_ANNUAL_LOCAL_LEAVE = 3;
    const TYPE_ANNUAL_SICK_LEAVE = 4;
    const TYPE_APPLIED_LEAVE = 5;
    const TYPE_CARRY_FORWARD_LOCAL_BALANCE = 6;
    const TYPE_FREEZE_CARRY_FORWARD_LOCAL_BALANCE = 7;
    const TYPE_FREEZE_LOCAL_BALANCE = 8;
    
    const TYPE_PROBATION_LOCAL_LEAVE_DESC = 'User < 1 year: Local balance was %s, now %s';
    const TYPE_PROBATION_SICK_LEAVE_DESC = 'User < 1 year: Sick balance was %s, now %s';
    const TYPE_ANNUAL_LOCAL_LEAVE_DESC = 'User > 1 year: Local balance was %s, now %s';
    const TYPE_ANNUAL_SICK_LEAVE_DESC = 'User > 1 year: Sick balance was %s, now %s';
    const TYPE_APPLIED_LEAVE_DESC = 'Applied leave %s: User %s balance was %s, now %s%s';
    const TYPE_CARRY_FORWARD_LOCAL_BALANCE_DESC = '31 Dec, User > 1 year: Local balance was %s, now %s - Carry forward balance was %s, now %s';
    const TYPE_FREEZE_CARRY_FORWARD_LOCAL_BALANCE_DESC = '31 March, User: Carry forward balance was %s, now %s - Frozen carry forward balance was %s, now %s';
    const TYPE_FREEZE_LOCAL_BALANCE_DESC = '31 Dec, User < 1 year: Local balance was %s, now %s - Frozen local balance was %s, now %s';
    
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
     * @ORM\Column(name="carry_forward_amount", type="float", nullable=true)
     */
    private $carryForwardAmount;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint", nullable=true)
     */
    private $type;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

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
    
    public function __construct($description = null, $user = null, $createdBy = 'system', $type = self::TYPE_APPLIED_LEAVE, $carryForwardAmount = null, $leave = null)
    {
        $this->leave = $leave;
        $this->user = $user;
        $this->description = $description;
        $this->createdAt = new \DateTime();
        $this->createdBy = $createdBy;
        $this->type = $type == null ? self::TYPE_APPLIED_LEAVE : $type;
        $this->carryForwardAmount = $carryForwardAmount;
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
     * @param integer $type
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
     * @return integer
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
    
    /**
     * @param string $description
     *
     * @return BalanceLog
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @return float|null
     */
    public function getCarryForwardAmount()
    {
        return $this->carryForwardAmount;
    }
    
    /**
     * @param float|null $carryForwardAmount
     *
     * @return BalanceLog
     */
    public function setCarryForwardAmount($carryForwardAmount)
    {
        $this->carryForwardAmount = $carryForwardAmount;
        return $this;
    }

}
