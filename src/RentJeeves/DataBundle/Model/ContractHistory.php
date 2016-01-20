<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Enum\ContractStatus;
use JMS\Serializer\Annotation as Serializer;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use RentJeeves\DataBundle\Enum\PaymentAccepted;

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
     *     type="PaymentAccepted",
     *     nullable=false,
     *     name="payment_accepted",
     *     options={
     *         "default"="0"
     *     }
     * )
     */
    protected $paymentAccepted = PaymentAccepted::ANY;

    /**
     * @ORM\Column(
     *     type="boolean",
     *     nullable=false,
     *     name="payment_allowed",
     *     options={
     *         "default"="1"
     *     }
     * )
     */
    protected $paymentAllowed = true;

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
     * @param \DateTime $paidTo
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
     * @return \DateTime
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
     * @param \DateTime $startAt
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
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set finishAt
     *
     * @param \DateTime $finishAt
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
     * @return \DateTime
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * @return integer
     */
    public function getPaymentAccepted()
    {
        return $this->paymentAccepted;
    }

    /**
     * @param integer $paymentAccepted
     */
    public function setPaymentAccepted($paymentAccepted)
    {
        $this->paymentAccepted = $paymentAccepted;
    }

    /**
     * @return boolean
     */
    public function isPaymentAllowed()
    {
        return $this->paymentAllowed;
    }

    /**
     * @param boolean $paymentAllowed
     */
    public function setPaymentAllowed($paymentAllowed)
    {
        $this->paymentAllowed = (bool) $paymentAllowed;
    }

}
