<?php

namespace LeavesOvertimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LeavesOvertimeBundle\Validator\Constraints as CustomAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Leaves
 *
 * @ORM\Table(name="axa_leaves")
 * @ORM\Entity(repositoryClass="LeavesOvertimeBundle\Repository\LeavesRepository")
 * @CustomAssert\ValidOldValue
 */
class Leaves extends EntityBase
{
    const STATUS_REQUESTED = 'Requested';
//    const STATUS_WITHDRAWN = 'Withdrawn';
    const STATUS_APPROVED = 'Approved';
    const STATUS_REJECTED = 'Rejected';
    const STATUS_CANCELLED = 'Cancelled';
    
    const TYPE_LOCAL_LEAVE = 'Local leave';
    const TYPE_SICK_LEAVE = 'Sick leave';
    const TYPE_ABSENCE_FROM_WORK = 'Absence from work';
    const TYPE_LEAVE_WITHOUT_PAY = 'Leave without pay';
    const TYPE_SPECIAL_PAID_LEAVE = 'Special paid leave';
    const TYPE_MATERNITY_LEAVE = 'Maternity leave';
    const TYPE_MATERNITY_LEAVE_WITHOUT_PAY = 'Maternity leave without pay';
    const TYPE_PATERNITY_LEAVE = 'Paternity leave';
    const TYPE_PATERNITY_LEAVES_WITHOUT_PAY = 'Paternity leaves without pay';
    const TYPE_COMPASSIONATE_LEAVE = 'Compassionate leave';
    const TYPE_WEDDING_LEAVE = 'Wedding leave';
    const TYPE_WEDDING_LEAVE_WITHOUT_PAY = 'Wedding leave without pay';
    const TYPE_INJURY_LEAVE = 'Injury leave';
    const TYPE_INJURY_LEAVE_WITHOUT_PAY = 'Injury leave without pay';
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User", inversedBy="leaves")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\OneToMany(targetEntity="LeavesOvertimeBundle\Entity\BalanceLog", mappedBy="leave")
     */
    private $balanceLogs;
    
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
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    public $startDate;
    
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     * @Assert\Expression("value == this.startDate", message="End date must be equal to start date.")
     */
    private $endDate;
    
    /**
     * @var float|null
     *
     * @ORM\Column(name="duration", type="float", nullable=true)
     */
    private $duration;
    
    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->balanceLogs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function getHours() {
        return $this->duration * 8;
    }
    
    public function __toString()
    {
        return (string)sprintf('%s - %s: %s', $this->getType(), $this->getStatus(), $this->getDuration());
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
    
    /**
     * @param float|null $duration
     *
     * @return Leaves
     */
    public function setDuration($duration) {
        $this->duration = $duration;
        return $this;
}
    
    /**
     * @return float|null
     */
    public function getDuration() {
        return $this->duration;
    }
    
    /**
     * @return array
     */
    public function getTypeChoices() {
        return [
            'Local leave' => $this::TYPE_LOCAL_LEAVE,
            'Sick leave' => $this::TYPE_SICK_LEAVE,
            'Absence from work' => $this::TYPE_ABSENCE_FROM_WORK,
            'Leave without pay' => $this::TYPE_LEAVE_WITHOUT_PAY,
            'Special paid leave' => $this::TYPE_SPECIAL_PAID_LEAVE,
            'Maternity leave' => $this::TYPE_MATERNITY_LEAVE,
            'Maternity leave without pay' => $this::TYPE_MATERNITY_LEAVE_WITHOUT_PAY,
            'Paternity leave' => $this::TYPE_PATERNITY_LEAVE,
            'Paternity leaves without pay' => $this::TYPE_PATERNITY_LEAVES_WITHOUT_PAY,
            'Compassionate leave' => $this::TYPE_COMPASSIONATE_LEAVE,
            'Wedding leave' => $this::TYPE_WEDDING_LEAVE,
            'Wedding leave without pay' => $this::TYPE_WEDDING_LEAVE_WITHOUT_PAY,
            'Injury leave' => $this::TYPE_INJURY_LEAVE,
            'Injury leave without pay' => $this::TYPE_INJURY_LEAVE_WITHOUT_PAY,
        ];
    }
    
    /**
     * @return array
     */
    public function getStatusChoices()
    {
        return [
            'Requested' => $this::STATUS_REQUESTED,
            //            'Withdrawn' => $this::STATUS_WITHDRAWN,
            'Approved' => $this::STATUS_APPROVED,
            'Rejected' => $this::STATUS_REJECTED,
            'Cancelled' => $this::STATUS_CANCELLED,
        ];
    }

    /**
     * Add balanceLog.
     *
     * @param \LeavesOvertimeBundle\Entity\BalanceLog $balanceLog
     *
     * @return Leaves
     */
    public function addBalanceLog(\LeavesOvertimeBundle\Entity\BalanceLog $balanceLog)
    {
        $this->balanceLogs[] = $balanceLog;

        return $this;
    }

    /**
     * Remove balanceLog.
     *
     * @param \LeavesOvertimeBundle\Entity\BalanceLog $balanceLog
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeBalanceLog(\LeavesOvertimeBundle\Entity\BalanceLog $balanceLog)
    {
        return $this->balanceLogs->removeElement($balanceLog);
    }

    /**
     * Get balanceLogs.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBalanceLogs()
    {
        return $this->balanceLogs;
    }
}
