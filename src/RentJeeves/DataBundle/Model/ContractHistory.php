<?php
namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\ContractStatus;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use DateTime;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class ContractHistory extends AbstractLogEntry
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="editor_id", type="bigint", nullable=true)
     */
    protected $editorId;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="bigint", nullable=true)
     */
    protected $objectId;

    /**
     * @ORM\ManyToOne(targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *      inversedBy="histories",
     *      cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id")
     */
    protected $object;

    /**
     * @ORM\Column(
     *     type="ContractStatus",
     *     options={
     *         "default"="pending"
     *     }
     * )
     */
    protected $status;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=true
     * )
     */
    protected $rent = null;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=true,
     *     name="uncollected_balance"
     * )
     */
    protected $uncollectedBalance;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=false,
     *     name="balance",
     *     options={
     *          "default":"0.00"
     *     }
     * )
     */
    protected $balance = 0.00;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=10,
     *     scale=2,
     *     nullable=false,
     *     name="integrated_balance",
     *     options={
     *          "default":"0.00"
     *     }
     * )
     */
    protected $integratedBalance = 0.00;

    /**
     * @ORM\Column(
     *     name="paid_to",
     *     type="date",
     *     nullable=true
     * )
     * @Serializer\SerializedName("paidTo")
     */
    protected $paidTo;

    /**
     * @ORM\Column(
     *     type="boolean",
     *     nullable=true,
     *     options={
     *         "default"="0"
     *     }
     * )
     */
    protected $reporting = 0;

    /**
     * @ORM\Column(
     *     name="start_at",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $startAt;

    /**
     * @ORM\Column(
     *     name="finish_at",
     *     type="date",
     *     nullable=true
     * )
     */
    protected $finishAt;

    /**
     * ORM\Column(name="object_class", type="string", length=255)
     * Exclude from DB schema
     *
     * @var string $objectClass
     */
    protected $objectClass;

    /**
     * ORM\Column(type="bigint")
     * Exclude from DB schema
     *
     * @var integer $version
     */
    protected $version;

    /**
     * ORM\Column(type="array", nullable=true)
     * Exclude from DB schema
     *
     * @var string $data
     */
    protected $data;

    /**
     * ORM\Column(length=255, nullable=true)
     * Exclude from DB schema
     *
     * @var string $data
     */
    protected $username;

    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set editorId
     *
     * @param integer $editorId
     * @return $this
     */
    public function setEditorId($editorId)
    {
        $this->editorId = $editorId;

        return $this;
    }

    /**
     * Get editorId
     *
     * @return integer
     */
    public function getEditorId()
    {
        return $this->editorId;
    }

    /**
     * @param $object
     *
     * @return $this
     */
    public function setObject($object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Contract
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Unit
     */
    public function setStatus($status = ContractStatus::PENDING)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set rent
     *
     * @param double $rent
     * @return Unit
     */
    public function setRent($rent)
    {
        $this->rent = $rent;
        return $this;
    }

    /**
     * Get rent
     *
     * @return double
     */
    public function getRent()
    {
        return $this->rent;
    }

    /**
     * @param float $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param float $integratedBalance
     */
    public function setIntegratedBalance($integratedBalance)
    {
        $this->integratedBalance = $integratedBalance;
    }

    /**
     * @return float
     */
    public function getIntegratedBalance()
    {
        return $this->integratedBalance;
    }


    /**
     * @param float $uncollectedBalance
     */
    public function setUncollectedBalance($uncollectedBalance)
    {
        $this->uncollectedBalance = $uncollectedBalance;
    }

    /**
     * @return float
     */
    public function getUncollectedBalance()
    {
        return $this->uncollectedBalance;
    }

    /**
     * Set Paid to
     *
     * @param DateTime $paidTo
     * @return $this
     */
    public function setPaidTo($paidTo)
    {
        $this->paidTo = $paidTo;
        return $this;
    }

    /**
     * Get startAt
     *
     * @return DateTime
     */
    public function getPaidTo()
    {
        $date = $this->paidTo;
        if (empty($date)) {
            $date = $this->getStartAt();
        }
        return $date;
    }

    /**
     * Set Reporting
     *
     * @param boolean $reporting
     * @return $this
     */
    public function setReporting($reporting)
    {
        $this->reporting = $reporting;
        return $this;
    }

    /**
     * Get Reporting
     *
     * @return boolean
     */
    public function getReporting()
    {
        return $this->reporting;
    }

    /**
     * Set startAt
     *
     * @param DateTime $startAt
     * @return $this
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
        return $this;
    }

    /**
     * Get startAt
     *
     * @return DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set finishAt
     *
     * @param DateTime $finishAt
     * @return $this
     */
    public function setFinishAt($finishAt)
    {
        $this->finishAt = $finishAt;
        return $this;
    }

    /**
     * Get finishAt
     *
     * @return DateTime
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * Set updatedAt
     *
     * @param DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add payment
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $operation
     * @return $this
     */
    public function addOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->operations[] = $operation;
        return $this;
    }

    /**
     * Remove payment
     *
     * @param \CreditJeeves\DataBundle\Entity\Operation $operation
     */
    public function removeOperation(\CreditJeeves\DataBundle\Entity\Operation $operation)
    {
        $this->operations->removeElement($operation);
    }

    /**
     * Get operations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Add payment
     *
     * @param \RentJeeves\DataBundle\Entity\Payment $payment
     * @return $this
     */
    public function addPayment(\RentJeeves\DataBundle\Entity\Payment $payment)
    {
        $this->payments[] = $payment;
        return $this;
    }

    /**
     * Remove payment
     *
     * @param \RentJeeves\DataBundle\Entity\Payment $opeartion
     */
    public function removePayment(\RentJeeves\DataBundle\Entity\Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }
}
