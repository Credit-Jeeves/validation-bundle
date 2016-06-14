<?php

namespace RentJeeves\LandlordBundle\MergingContracts;

use RentJeeves\CoreBundle\Services\PhoneNumberFormatter;
use RentJeeves\DataBundle\Entity\Contract;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

abstract class BaseMergingDTO
{
    const MATCH_EMAIL_TYPE = 'email';

    const MATCH_RESIDENT_TYPE = 'resident ID';

    const NO_MATCH_TYPE = 'none';

    /**
     * @var Contract
     * @Serializer\Exclude
     */
    protected $originalContract;

    /**
     * @var Contract
     * @Serializer\Exclude
     */
    protected $duplicateContract;

    /**
     * @var string
     * @Serializer\Exclude
     */
    protected $matchingType;

    /**
     * @var string
     * @Serializer\SerializedName("firstName")
     * @Serializer\Type("string")
     * @Serializer\AccessType("public_method")
     * @Assert\NotBlank(message="contract.merging.error.first_name.empty")
     */
    protected $tenantFirstName;

    /**
     * @var string
     * @Serializer\SerializedName("lastName")
     * @Serializer\Type("string")
     * @Serializer\AccessType("public_method")
     * @Assert\NotBlank(message="contract.merging.error.last_name.empty")
     */
    protected $tenantLastName;

    /**
     * @var string
     * @Serializer\SerializedName("email")
     * @Serializer\Type("string")
     * @Serializer\AccessType("public_method")
     * @Assert\NotBlank(message="contract.merging.error.email.empty")
     */
    protected $tenantEmail;

    /**
     * @var string
     * @Serializer\SerializedName("phone")
     * @Serializer\Type("string")
     * @Serializer\AccessType("public_method")
     */
    protected $tenantPhone;

    /**
     * @var string
     * @Serializer\SerializedName("residentId")
     * @Serializer\Type("string")
     * @Serializer\AccessType("public_method")
     */
    protected $contractResidentId;

    /**
     * @var string
     * @Serializer\SerializedName("leaseId")
     * @Serializer\Type("string")
     * @Serializer\AccessType("public_method")
     */
    protected $contractLeaseId;

    /**
     * @var float
     * @Serializer\SerializedName("amount")
     * @Serializer\Type("float")
     * @Serializer\AccessType("public_method")
     * @Assert\NotBlank(message="contract.merging.error.rent.empty")
     * @Assert\Range(min=1, minMessage="contract.merging.error.rent.empty")
     */
    protected $contractRent;

    /**
     * @var float
     * @Serializer\SerializedName("balance")
     * @Serializer\Type("float")
     * @Serializer\AccessType("public_method")
     */
    protected $contractIntegratedBalance;
    /**
     * @var integer
     * @Serializer\SerializedName("dueDate")
     * @Serializer\Type("integer")
     * @Serializer\AccessType("public_method")
     * @Assert\NotBlank(message="contract.merging.error.due_date.empty")
     */
    protected $contractDueDate;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("start")
     * @Serializer\Type("DateTime<'m/d/Y'>")
     * @Serializer\AccessType("public_method")
     * @Assert\NotBlank(message="contract.merging.error.start.empty")
     */
    protected $contractStartAt;

    /**
     * @var \DateTime
     * @Serializer\SerializedName("finish")
     * @Serializer\Type("DateTime<'m/d/Y'>")
     * @Serializer\AccessType("public_method")
     */
    protected $contractFinishAt;

    /**
     * @var int
     * @Serializer\SerializedName("propertyId")
     * @Serializer\Type("integer")
     * @Serializer\AccessType("public_method")
     * @Assert\NotBlank(message="contract.merging.error.property.invalid")
     */
    protected $contractPropertyId;

    /**
     * @var int
     * @Serializer\SerializedName("unitId")
     * @Serializer\Type("integer")
     * @Serializer\AccessType("public_method")
     */
    protected $contractUnitId;

    /**
     * @param string $firstName
     */
    public function setTenantFirstName($firstName)
    {
        $this->tenantFirstName = $firstName;
    }

    /**
     * @return string
     */
    public function getTenantFirstName()
    {
        if (!$this->tenantFirstName && $this->getTenantDataContract()) {
            $this->tenantFirstName = $this->getTenantDataContract()->getTenant()->getFirstName();
        }

        return $this->tenantFirstName;
    }

    /**
     * @param string $lastName
     */
    public function setTenantLastName($lastName)
    {
        $this->tenantLastName = $lastName;
    }

    /**
     * @return string
     */
    public function getTenantLastName()
    {
        if (!$this->tenantLastName && $this->getTenantDataContract()) {
            $this->tenantLastName = $this->getTenantDataContract()->getTenant()->getLastName();
        }

        return $this->tenantLastName;
    }

    /**
     * @param string $phone
     */
    public function setTenantPhone($phone)
    {
        $this->tenantPhone = PhoneNumberFormatter::formatToDigitsOnly($phone);
    }

    /**
     * @return string
     */
    public function getTenantPhone()
    {
        if (!$this->tenantPhone && $this->getTenantDataContract()) {
            $this->tenantPhone = $this->getTenantDataContract()->getTenant()->getPhone();
        }

        return $this->tenantPhone;
    }

    /**
     * @param string $email
     */
    public function setTenantEmail($email)
    {
        $this->tenantEmail = $email;
    }

    /**
     * @return string
     */
    public function getTenantEmail()
    {
        if (!$this->tenantEmail && $this->getTenantDataContract()) {
            $this->tenantEmail = $this->getTenantDataContract()->getTenant()->getEmail();
        }

        return $this->tenantEmail;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("isAllowedEditResidentId")
     *
     * @return boolean
     */
    public function isAllowedEditResidentId()
    {
        return $this->getLeaseDataContract() ?
            $this->getLeaseDataContract()->getGroup()->isAllowedEditResidentId() :
            false;
    }

    /**
     * @param string $residentId
     */
    public function setContractResidentId($residentId)
    {
        $this->contractResidentId = $residentId;
    }

    /**
     * @return string
     */
    public function getContractResidentId()
    {
        if (!$this->contractResidentId &&
            $this->isAllowedEditResidentId() &&
            $residentMapping = $this->getLeaseDataContract()->getTenant()->getResidentForHolding(
                $this->getLeaseDataContract()->getHolding()
            )
        ) {
            $this->contractResidentId = $residentMapping->getResidentId();
        }

        return $this->contractResidentId;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("isAllowedEditLeaseId")
     *
     * @return boolean
     */
    public function isAllowedEditLeaseId()
    {
        return $this->getLeaseDataContract() ?
            $this->getLeaseDataContract()->getGroup()->isAllowedEditLeaseId() :
            false;
    }

    /**
     * @param string $leaseId
     */
    public function setContractLeaseId($leaseId)
    {
        $this->contractLeaseId = $leaseId;
    }

    /**
     * @return string
     */
    public function getContractLeaseId()
    {
        if (!$this->contractLeaseId && $this->isAllowedEditLeaseId()) {
            return $this->getLeaseDataContract()->getId();
        }

        return $this->contractLeaseId;
    }

    /**
     * @param float $rent
     */
    public function setContractRent($rent)
    {
        $this->contractRent = $rent;
    }

    /**
     * @return float
     */
    public function getContractRent()
    {
        if (!$this->contractRent && $this->getLeaseDataContract()) {
            $this->contractRent = $this->getLeaseDataContract()->getRent();
        }

        return $this->contractRent;
    }

    /**
     * @param int $contractDueDate
     */
    public function setContractDueDate($contractDueDate)
    {
        $this->contractDueDate = $contractDueDate;
    }

    /**
     * @return integer
     */
    public function getContractDueDate()
    {
        if (!$this->contractDueDate && $this->getLeaseDataContract()) {
            $this->contractDueDate = $this->getLeaseDataContract()->getDueDate();
        }

        return $this->contractDueDate;
    }

    /**
     * @param \DateTime $startAt
     */
    public function setContractStartAt($startAt)
    {
        $this->contractStartAt = $startAt;
    }

    /**
     * @return \DateTime
     */
    public function getContractStartAt()
    {
        if (!$this->contractStartAt && $this->getLeaseDataContract()) {
            $this->contractStartAt = $this->getLeaseDataContract()->getStartAt();
        }

        return $this->contractStartAt;
    }

    /**
     * @param \DateTime $finishAt
     */
    public function setContractFinishAt($finishAt)
    {
        $this->contractFinishAt = $finishAt;
    }

    /**
     * @return \DateTime
     */
    public function getContractFinishAt()
    {
        if (!$this->contractFinishAt && $this->getLeaseDataContract()) {
            $this->contractFinishAt = $this->getLeaseDataContract()->getFinishAt();
        }

        return $this->contractFinishAt;
    }

    /**
     * @param float $balance
     */
    public function setContractIntegratedBalance($balance)
    {
        $this->contractIntegratedBalance = $balance;
    }

    /**
     * @return float
     */
    public function getContractIntegratedBalance()
    {
        if (!$this->contractIntegratedBalance &&
            $this->getLeaseDataContract() &&
            $this->getLeaseDataContract()->getGroupSettings()->getIsIntegrated()
        ) {
            $this->contractIntegratedBalance = $this->getLeaseDataContract()->getIntegratedBalance();
        }

        return $this->contractIntegratedBalance;
    }

    /**
     * @param int $propertyId
     */
    public function setContractPropertyId($propertyId)
    {
        $this->contractPropertyId = $propertyId;
    }

    /**
     * @return int|null
     */
    public function getContractPropertyId()
    {
        if (!$this->contractPropertyId && $this->getLeaseDataContract()) {
            $this->contractPropertyId = $this->getLeaseDataContract()->getProperty() ?
                $this->getLeaseDataContract()->getProperty()->getId() :
                null;
        }

        return $this->contractPropertyId;
    }

    /**
     * @param int $unitId
     */
    public function setContractUnitId($unitId)
    {
        $this->contractUnitId = $unitId;
    }

    /**
     * @return int|null
     */
    public function getContractUnitId()
    {
        if (!$this->contractUnitId && $this->getLeaseDataContract()) {
            $this->contractUnitId = $this->getLeaseDataContract()->getUnit() ?
                $this->getLeaseDataContract()->getUnit()->getId() :
                null;
        }

        return $this->contractUnitId;
    }

    /**
     * @param string $matchingType
     */
    public function setMatchingType($matchingType)
    {
        $this->matchingType = $matchingType;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("matchingType")
     *
     * @return string
     */
    public function getMatchingType()
    {
        if ($this->matchingType) {
            return $this->matchingType;
        }

        $this->matchingType = self::NO_MATCH_TYPE;

        if ($this->originalContract->getTenantEmail() === $this->duplicateContract->getTenantEmail()) {
            $this->matchingType = self::MATCH_EMAIL_TYPE;
        } elseif ($this->originalContract->getGroup()->isAllowedEditResidentId() and
            $residentMappingOrigin = $this->originalContract->getTenant()->getResidentForHolding(
                $this->originalContract->getHolding()
            ) and $this->duplicateContract->getGroup()->isAllowedEditResidentId() and
            $residentMappingDuplicate = $this->duplicateContract->getTenant()->getResidentForHolding(
                $this->duplicateContract->getHolding()
            ) and $residentMappingOrigin->getResidentId() === $residentMappingDuplicate->getResidentId()
        ) {
            $this->matchingType = self::MATCH_RESIDENT_TYPE;
        }

        return $this->matchingType;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("duplicateTenantInfo")
     *
     * @return string
     */
    public function getDuplicateTenantInfo()
    {
        $tenantInfo = '';

        if ($this->duplicateContract) {
            $tenantInfo = $this->duplicateContract->getTenantFullName();

            if (self::MATCH_RESIDENT_TYPE === $this->getMatchingType()) {
                $tenantInfo = sprintf('%s (%s)', $tenantInfo, $this->duplicateContract->getTenantEmail());
            }
        }

        return $tenantInfo;
    }

    /**
     * @return int|null
     */
    public function getOriginalContractId()
    {
        if ($this->originalContract) {
            return $this->originalContract->getId();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("duplicateContractId")
     * @Assert\NotBlank
     *
     * @return integer|null
     */
    public function getDuplicateContractId()
    {
        if ($this->duplicateContract) {
            return $this->duplicateContract->getId();
        }

        return null;
    }

    /**
     * @return Contract|null
     */
    abstract protected function getTenantDataContract();

    /**
     * @return Contract|null
     */
    abstract protected function getLeaseDataContract();
}
