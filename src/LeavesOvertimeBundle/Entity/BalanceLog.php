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
     * @var \LeavesOvertimeBundle\Entity\Leaves|null
     *
     * @ORM\ManyToOne(targetEntity="LeavesOvertimeBundle\Entity\Leaves", inversedBy="balanceLogs")
     * @ORM\JoinColumn(name="leaves_id", referencedColumnName="id")
     */
    private $leaves;

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
    
    public function __construct($leave = null, $previousBalance = null, $newBalance = null, $createdBy = null)
    {
        $this->leaves = $leave;
        $this->previousBalance = $previousBalance;
        $this->newBalance = $newBalance;
        $this->createdAt = new \DateTime();
        $this->createdBy = $createdBy;
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
     * Set leaves.
     *
     * @param \LeavesOvertimeBundle\Entity\Leaves|null $leaves
     *
     * @return BalanceLog
     */
    public function setLeaves(\LeavesOvertimeBundle\Entity\Leaves $leaves = null)
    {
        $this->leaves = $leaves;

        return $this;
    }

    /**
     * Get leaves.
     *
     * @return \LeavesOvertimeBundle\Entity\Leaves|null
     */
    public function getLeaves()
    {
        return $this->leaves;
    }
}
