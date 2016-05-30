<?php
namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RentJeeves\DataBundle\Enum\ImportLeaseResidentStatus;
use RentJeeves\DataBundle\Enum\ImportLeaseUserStatus;
use RentJeeves\DataBundle\Enum\PaymentAccepted;

/**
 * @ORM\MappedSuperclass
 */
abstract class ImportLease
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="tenant_email", type="string", length=255, nullable=true)
     */
    protected $tenantEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    protected $dateOfBirth;

    /**
     * @var string
     *
     * @ORM\Column(
     *      name="external_resident_id",
     *      type="string",
     *      length=128,
     *      nullable=true
     * )
     */
    protected $externalResidentId;

    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\DataBundle\Entity\Group", inversedBy="importLease")
     * @ORM\JoinColumn(
     *      name="group_id",
     *      referencedColumnName="id",
     *      nullable=true
     * )
     * @var \CreditJeeves\DataBundle\Entity\Group
     */
    protected $group;

    /**
     * @var string
     *
     * @ORM\Column(
     *      type="string",
     *      name="external_property_id",
     *      length=128,
     *      nullable=true
     * )
     */
    protected $externalPropertyId;

    /**
     * @var string
     *
     * @ORM\Column(
     *      type="string",
     *      name="external_building_id",
     *      length=128,
     *      nullable=true
     * )
     */
    protected $externalBuildingId;

    /**
     * @var string
     *
     * @ORM\Column(
     *      type="string",
     *      name="external_unit_id",
     *      length=128,
     *      nullable=false
     * )
     */
    protected $externalUnitId;

    /**
     * @var string
     *
     * @ORM\Column(
     *     type="ImportLeaseResidentStatus",
     *     name="resident_status",
     *     nullable=true
     * )
     */
    protected $residentStatus;

    /**
     * @ORM\Column(
     *     type="PaymentAccepted",
     *     name="payment_accepted",
     *     nullable=true
     * )
     */
    protected $paymentAccepted;

    /**
     * @var int
     *
     * @ORM\Column(name="due_date", type="integer", nullable=true)
     */
    protected $dueDate;

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
     *     nullable=false,
     *     name="integrated_balance",
     *     options={
     *          "default":"0.00"
     *     }
     * )
     */
    protected $integratedBalance;

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
    protected $finishAt = null;

    /**
     * @ORM\Column(
     *     name="external_lease_id",
     *     type="string",
     *     nullable=true
     * )
     */
    protected $externalLeaseId;

    /**
     * @ORM\ManyToOne(
     *      targetEntity="RentJeeves\DataBundle\Entity\Import",
     *      inversedBy="importLeases"
     * )
     * @ORM\JoinColumn(
     *      name="import_id",
     *      referencedColumnName="id",
     *      nullable=false
     * )
     * @var \RentJeeves\DataBundle\Entity\Import
     */
    protected $import;

    /**
     * @var string
     *
     * @ORM\Column(
     *     type="ImportLeaseUserStatus",
     *     name="user_status",
     *     nullable=true
     * )
     */
    protected $userStatus;

    /**
     * @var string
     *
     * @ORM\Column(
     *     type="ImportLeaseStatus",
     *     name="lease_status",
     *     nullable=true
     * )
     */
    protected $leaseStatus;

    /**
     * @ORM\Column(
     *     name="error_messages",
     *     type="array",
     *     nullable=true
     * )
     * @var array
     */
    protected $errorMessages;

    /**
     * **
     * @ORM\Column(
     *     name="processed",
     *     type="boolean",
     *     options={
     *         "default"=0
     *     }
     * )
     * @var boolean
     */
    protected $processed = false;

    /**
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(name="created_at",type="datetime")
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(name="updated_at",type="datetime")
     */
    protected $updatedAt;

    /**
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param \CreditJeeves\DataBundle\Entity\Group $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTenantEmail()
    {
        return $this->tenantEmail;
    }

    /**
     * @param string $tenantEmail
     */
    public function setTenantEmail($tenantEmail)
    {
        $this->tenantEmail = $tenantEmail;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param mixed $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * @return string
     */
    public function getExternalResidentId()
    {
        return $this->externalResidentId;
    }

    /**
     * @param string $externalResidentId
     */
    public function setExternalResidentId($externalResidentId)
    {
        $this->externalResidentId = $externalResidentId;
    }

    /**
     * @return string
     */
    public function getExternalAccountId()
    {
        return $this->externalAccountId;
    }

    /**
     * @param string $externalAccountId
     */
    public function setExternalAccountId($externalAccountId)
    {
        $this->externalAccountId = $externalAccountId;
    }

    /**
     * @return string
     */
    public function getExternalPropertyId()
    {
        return $this->externalPropertyId;
    }

    /**
     * @param string $externalPropertyId
     */
    public function setExternalPropertyId($externalPropertyId)
    {
        $this->externalPropertyId = $externalPropertyId;
    }

    /**
     * @return string
     */
    public function getExternalBuildingId()
    {
        return $this->externalBuildingId;
    }

    /**
     * @param string $externalBuildingId
     */
    public function setExternalBuildingId($externalBuildingId)
    {
        $this->externalBuildingId = $externalBuildingId;
    }

    /**
     * @return string
     */
    public function getExternalUnitId()
    {
        return $this->externalUnitId;
    }

    /**
     * @param string $externalUnitId
     */
    public function setExternalUnitId($externalUnitId)
    {
        $this->externalUnitId = $externalUnitId;
    }

    /**
     * @return string
     */
    public function getResidentStatus()
    {
        return $this->residentStatus;
    }

    /**
     * @param ImportLeaseResidentStatus $residentStatus
     */
    public function setResidentStatus($residentStatus)
    {
        $this->residentStatus = $residentStatus;
    }

    /**
     * @return mixed
     */
    public function getPaymentAccepted()
    {
        return $this->paymentAccepted;
    }

    /**
     * @param PaymentAccepted $paymentAccepted
     */
    public function setPaymentAccepted($paymentAccepted)
    {
        $this->paymentAccepted = $paymentAccepted;
    }

    /**
     * @return int
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param int $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return string
     */
    public function getRent()
    {
        return $this->rent;
    }

    /**
     * @param string $rent
     */
    public function setRent($rent)
    {
        $this->rent = $rent;
    }

    /**
     * @return mixed
     */
    public function getIntegratedBalance()
    {
        return $this->integratedBalance;
    }

    /**
     * @param mixed $integratedBalance
     */
    public function setIntegratedBalance($integratedBalance)
    {
        $this->integratedBalance = $integratedBalance;
    }

    /**
     * @return \DateTime
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @param \DateTime $startAt
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
    }

    /**
     * @return \DateTime
     */
    public function getFinishAt()
    {
        return $this->finishAt;
    }

    /**
     * @param \DateTime $finishAt
     */
    public function setFinishAt($finishAt)
    {
        $this->finishAt = $finishAt;
    }

    /**
     * @return string
     */
    public function getExternalLeaseId()
    {
        return $this->externalLeaseId;
    }

    /**
     * @param string $externalLeaseId
     */
    public function setExternalLeaseId($externalLeaseId)
    {
        $this->externalLeaseId = $externalLeaseId;
    }

    /**
     * @return string
     */
    public function getUserStatus()
    {
        return $this->userStatus;
    }

    /**
     * @param ImportLeaseUserStatus $userStatus
     */
    public function setUserStatus($userStatus)
    {
        $this->userStatus = $userStatus;
    }

    /**
     * @return ImportLeaseUserStatus
     */
    public function getLeaseStatus()
    {
        return $this->leaseStatus;
    }

    /**
     * @param ImportLeaseUserStatus $leaseStatus
     */
    public function setLeaseStatus($leaseStatus)
    {
        $this->leaseStatus = $leaseStatus;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @param array $errorMessages
     */
    public function setErrorMessages($errorMessages)
    {
        $this->errorMessages = $errorMessages;
    }

    /**
     * @return boolean
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * @param boolean $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\Import
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * @param \RentJeeves\DataBundle\Entity\Import $import
     */
    public function setImport(\RentJeeves\DataBundle\Entity\Import $import)
    {
        $this->import = $import;
    }
}
