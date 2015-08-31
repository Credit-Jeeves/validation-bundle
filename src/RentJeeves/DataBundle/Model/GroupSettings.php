<?php

namespace RentJeeves\DataBundle\Model;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Enum\PaymentProcessor;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class GroupSettings
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="bigint"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(
     *     type="PaymentProcessor",
     *     options={
     *         "default"="heartland"
     *     },
     *     name="payment_processor",
     *     nullable=false
     * )
     * @var string
     */
    protected $paymentProcessor = PaymentProcessor::HEARTLAND;

    /**
     * @ORM\Column(
     *     name="pid_verification",
     *     type="boolean"
     * )
     * @var boolean
     */
    protected $isPidVerificationSkipped = false;

    /**
     * @ORM\Column(
     *      type="boolean",
     *      name="is_integrated",
     *      options={
     *          "default":0
     *      }
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $isIntegrated = false;

    /**
     * @ORM\Column(
     *      type="boolean",
     *      name="is_reporting_off",
     *      options={
     *          "default":0
     *      }
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $isReportingOff = false;

    /**
     * @ORM\Column(
     *      type="boolean",
     *      name="pay_balance_only",
     *      options={
     *          "default":0
     *      }
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $payBalanceOnly = false;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Group",
     *     inversedBy="groupSettings",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false, unique=true)
     * @var Group
     */
    protected $group;

    /**
     * @ORM\Column(
     *      type="integer",
     *      name="due_date",
     *      options={
     *          "default":1
     *      },
     *      nullable=false
     * )
     */
    protected $dueDate = 1;

    /**
     * @ORM\Column(
     *      type="integer",
     *      name="open_date",
     *      options={
     *          "default":1
     *      },
     *      nullable=false
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $openDate = 1;

    /**
     * @ORM\Column(
     *      type="integer",
     *      name="close_date",
     *      options={
     *          "default":31
     *      },
     *      nullable=false
     * )
     * @Serializer\Groups({"payRent"})
     */
    protected $closeDate = 31;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=true
     * )
     * @Serializer\SerializedName("feeCC")
     * @Serializer\Groups({"payRent"})
     */
    protected $feeCC;

    /**
     * @ORM\Column(
     *      type="decimal",
     *      precision=10,
     *      scale=2,
     *      nullable=true
     * )
     * @Serializer\SerializedName("feeACH")
     * @Serializer\Groups({"payRent"})
     */
    protected $feeACH;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_passed_ach")
     *
     * @Serializer\SerializedName("isPassedACH")
     * @Serializer\Groups({"payRent"})
     */
    protected $passedAch = false;

    /**
     * @var boolean
     *
     * @ORM\Column(
     *     type="boolean",
     *     name="show_properties_tab",
     *     options={
     *         "default":1
     *      }
     * )
     *
     * @Serializer\Exclude
     */
    protected $showPropertiesTab = true;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *     name="created_at",
     *     type="datetime"
     * )
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(
     *     name="updated_at",
     *     type="datetime"
     * )
     * @var DateTime
     */
    protected $updatedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(
     *      type="boolean",
     *      name="auto_approve_contracts",
     *      options={
     *          "default" : 0
     *      }
     * )
     */
    protected $autoApproveContracts = false;

    /**
     * @param float $feeACH
     */
    public function setFeeACH($feeACH)
    {
        $this->feeACH = $feeACH;
    }

    /**
     * @return double
     */
    public function getFeeACH()
    {
        return $this->feeACH;
    }

    /**
     * @param double $feeCC
     */
    public function setFeeCC($feeCC)
    {
        $this->feeCC = $feeCC;
    }

    /**
     * @return double
     */
    public function getFeeCC()
    {
        return $this->feeCC;
    }

    /**
     * @return boolean
     */
    public function isPassedAch()
    {
        return $this->passedAch;
    }

    /**
     * @param boolean $passedAch
     */
    public function setPassedAch($passedAch)
    {
        $this->passedAch = $passedAch;
    }

    /**
     * @param boolean $payBalanceOnly
     */
    public function setPayBalanceOnly($payBalanceOnly)
    {
        $this->payBalanceOnly = $payBalanceOnly;
    }

    /**
     * @return boolean
     */
    public function getPayBalanceOnly()
    {
        return $this->payBalanceOnly;
    }

    /**
     * @param $paymentProcessor
     */
    public function setPaymentProcessor($paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    /**
     * @return string
     */
    public function getPaymentProcessor()
    {
        return $this->paymentProcessor;
    }

    /**
     * @param boolean $pidVerification
     */
    public function setIsPidVerificationSkipped($pidVerification)
    {
        $this->isPidVerificationSkipped = (boolean) $pidVerification;
    }

    /**
     * @return boolean
     */
    public function getIsPidVerificationSkipped()
    {
        return $this->isPidVerificationSkipped;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Group $group
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param boolean $isIntegrated
     */
    public function setIsIntegrated($isIntegrated)
    {
        $this->isIntegrated = (boolean) $isIntegrated;
    }

    /**
     * @return boolean
     */
    public function getIsIntegrated()
    {
        return $this->isIntegrated;
    }

    /**
     * @param integer $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return integer
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param integer $openDate
     */
    public function setOpenDate($openDate)
    {
        $this->openDate = $openDate;
    }

    /**
     * @return integer
     */
    public function getOpenDate()
    {
        return $this->openDate;
    }

    /**
     * @param integer $closeDate
     */
    public function setCloseDate($closeDate)
    {
        $this->closeDate = $closeDate;
    }

    /**
     * @return integer
     */
    public function getCloseDate()
    {
        return $this->closeDate;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return bool
     */
    public function getIsReportingOff()
    {
        return $this->isReportingOff;
    }

    /**
     * @param bool $isReportingOff
     */
    public function setIsReportingOff($isReportingOff)
    {
        $this->isReportingOff = $isReportingOff;
    }

    /**
     * @return boolean
     */
    public function isShowPropertiesTab()
    {
        return $this->showPropertiesTab;
    }

    /**
     * @param boolean $showPropertiesTab
     */
    public function setShowPropertiesTab($showPropertiesTab)
    {
        $this->showPropertiesTab = $showPropertiesTab;
    }

    /**
     * @return boolean
     */
    public function isAutoApproveContracts()
    {
        return $this->autoApproveContracts;
    }

    /**
     * @param boolean $isAutoApproveContracts
     */
    public function setAutoApproveContracts($isAutoApproveContracts)
    {
        $this->autoApproveContracts = $isAutoApproveContracts;
    }
}
