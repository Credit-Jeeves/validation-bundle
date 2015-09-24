<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Entity\DepositAccount as DepositAccountEntity;
use RentJeeves\DataBundle\Enum\PaymentType;
use Symfony\Component\Validator\Constraints as Assert;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\CoreBundle\DateTime;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass
 */
class Payment
{
    /**
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"payRent"})
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *      inversedBy="payments",
     *      cascade={"persist", "merge"}
     * )
     * @ORM\JoinColumn(
     *      name="contract_id",
     *      referencedColumnName="id",
     *      nullable=false
     * )
     *
     * @var Contract
     */
    protected $contract;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\PaymentAccount",
     *      inversedBy="payments",
     *      cascade={"persist", "remove"}
     * )
     * @ORM\JoinColumn(
     *      name="payment_account_id",
     *      referencedColumnName="id",
     *      nullable=false,
     *      onDelete="CASCADE"
     * )
     * @Serializer\SerializedName("paymentAccountId")
     * @Serializer\Accessor(getter="getPaymentAccountId")
     * @Serializer\Groups({"payRent"})
     *
     * @var PaymentAccount
     */
    protected $paymentAccount;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\DepositAccount",
     *      inversedBy="payments",
     *      cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *      name="deposit_account_id",
     *      referencedColumnName="id"
     * )
     * @Serializer\Exclude
     *
     * @var DepositAccountEntity
     */
    protected $depositAccount;

    /**
     * @ORM\Column(type="PaymentType")
     * @Assert\NotBlank(
     *      message="checkout.error.type.empty"
     * )
     * @Serializer\Groups({"payRent"})
     *
     * @var PaymentType
     */
    protected $type;

    /**
     * @ORM\Column(type="PaymentStatus")
     * @Assert\NotBlank(
     *      message="checkout.error.status.empty"
     * )
     * @Serializer\Groups({"payRent"})
     *
     * @var PaymentStatus
     */
    protected $status = PaymentStatus::ACTIVE;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=true
     * )
     * @Assert\Range(
     *      min=0,
     *      minMessage="checkout.error.amount.min",
     *      invalidMessage="checkout.error.amount.valid",
     *      groups={"Default", "pay_anything"}
     * )
     * @Serializer\Groups({"payRent"})
     *
     * @var double
     */
    protected $amount;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=false
     * )
     * @Assert\NotBlank(
     *      message="checkout.error.total.empty"
     * )
     * @Assert\Range(
     *      min=1,
     *      minMessage="checkout.error.total.min",
     *      invalidMessage="checkout.error.total.valid",
     *      groups={"Default", "pay_anything"}
     * )
     * @Serializer\Groups({"payRent"})
     *
     * @var double
     */
    protected $total;

    /**
     * @ORM\Column(name="paid_for", type="date", nullable=true)
     * @Serializer\SerializedName("paidFor")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({"payRent"})
     *
     * @var DateTime
     */
    protected $paidFor;

    /**
     * @ORM\Column(name="due_date", type="integer")
     * @Assert\NotBlank(
     *      message="checkout.error.dueDate.empty",
     *      groups={"paymentWizard"}
     * )
     * @Assert\Range(min = 1, max = 31)
     *
     * @Serializer\SerializedName("dueDate")
     * @Serializer\Groups({"payRent"})
     *
     * @var int
     */
    protected $dueDate;

    /**
     * @ORM\Column(name="start_month", type="integer")
     * @Assert\NotBlank(
     *      message="checkout.error.startMonth.empty",
     *      groups={"paymentWizard"}
     * )
     * @Assert\Range(min = 1, max = 12)
     * @Serializer\SerializedName("startMonth")
     * @Serializer\Groups({"payRent"})
     *
     * @var int
     */
    protected $startMonth;

    /**
     * @ORM\Column(name="start_year", type="integer")
     * @Assert\NotBlank(
     *      message="checkout.error.startYear.empty",
     *      groups={"paymentWizard"}
     * )
     * @Serializer\SerializedName("startYear")
     * @Serializer\Groups({"payRent"})
     *
     * @var int
     */
    protected $startYear;

    /**
     * @ORM\Column(name="end_month", type="integer", nullable=true)
     * @Assert\NotBlank(
     *      message="checkout.error.endMonth.empty",
     *      groups={"cancelled_on"}
     * )
     * @Assert\Range(min = 1, max = 12)
     * @Serializer\SerializedName("endMonth")
     * @Serializer\Groups({"payRent"})
     *
     * @var int
     */
    protected $endMonth = null;

    /**
     * @ORM\Column(name="end_year", type="integer", nullable=true)
     * @Assert\NotBlank(
     *      message="checkout.error.endYear.empty",
     *      groups={"cancelled_on"}
     * )
     * @Serializer\SerializedName("endYear")
     * @Serializer\Groups({"payRent"})
     *
     * @var int
     */
    protected $endYear = null;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity = "JobRelatedPayment", mappedBy = "payment")
     */
    protected $jobs;

    /**
     * @ORM\Column(name="close_details", type="array", nullable=true)
     */
    protected $closeDetails;

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
     * Set type
     *
     * @param PaymentType $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return $thisType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status.
     *
     * @param PaymentStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return PaymentStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amountOther + amount
     *
     * @param float $amount
     * @return $this
     */
    public function setTotal($amount)
    {
        $this->total = $amount;

        return $this;
    }

    /**
     * Get amountOther + amount
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param DateTime $paidFor
     * @return $this
     */
    public function setPaidFor($paidFor)
    {
        $this->paidFor = $paidFor;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPaidFor()
    {
        return $this->paidFor;
    }

    /**
     * Set dueDate
     *
     * @param integer $dueDate
     * @return $this
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return integer
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set startMonth
     *
     * @param integer $startMonth
     * @return $this
     */
    public function setStartMonth($startMonth)
    {
        $this->startMonth = $startMonth;

        return $this;
    }

    /**
     * Get startMonth
     *
     * @return integer
     */
    public function getStartMonth()
    {
        return $this->startMonth;
    }

    /**
     * Set startYear
     *
     * @param integer $startYear
     * @return $this
     */
    public function setStartYear($startYear)
    {
        $this->startYear = $startYear;

        return $this;
    }

    /**
     * Get startYear
     *
     * @return integer
     */
    public function getStartYear()
    {
        return $this->startYear;
    }

    /**
     * Set endMonth
     *
     * @param integer $endMonth
     * @return $this
     */
    public function setEndMonth($endMonth)
    {
        $this->endMonth = $endMonth;

        return $this;
    }

    /**
     * Get endMonth
     *
     * @return integer
     */
    public function getEndMonth()
    {
        return $this->endMonth;
    }

    /**
     * Set endYear
     *
     * @param integer $endYear
     * @return $this
     */
    public function setEndYear($endYear)
    {
        $this->endYear = $endYear;

        return $this;
    }

    /**
     * Get endYear
     *
     * @return integer
     */
    public function getEndYear()
    {
        return $this->endYear;
    }

    /**
     * Set createdAt
     *
     * @param DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
     * Set contract
     *
     * @param \RentJeeves\DataBundle\Entity\Contract $contract
     * @return $this
     */
    public function setContract(\RentJeeves\DataBundle\Entity\Contract $contract = null)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return \RentJeeves\DataBundle\Entity\Contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Set PaymentAccount
     *
     * @param \RentJeeves\DataBundle\Entity\PaymentAccount $paymentAccount
     * @return $this
     */
    public function setPaymentAccount(\RentJeeves\DataBundle\Entity\PaymentAccount $paymentAccount = null)
    {
        $this->paymentAccount = $paymentAccount;

        return $this;
    }

    /**
     * Get PaymentAccount
     *
     * @return \RentJeeves\DataBundle\Entity\PaymentAccount
     */
    public function getPaymentAccount()
    {
        return $this->paymentAccount;
    }

    /**
     * @return array
     */
    public function getCloseDetails()
    {
        return $this->closeDetails;
    }

    /**
     * @param mixed $closeDetails
     */
    public function setCloseDetails(array $closeDetails)
    {
        $this->closeDetails = $closeDetails;
    }

    /**
     * @return DepositAccountEntity
     */
    public function getDepositAccount()
    {
        return $this->depositAccount;
    }

    /**
     * @param DepositAccountEntity $depositAccount
     */
    public function setDepositAccount(DepositAccountEntity $depositAccount)
    {
        $this->depositAccount = $depositAccount;
    }
}
