<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use CreditJeeves\DataBundle\Enum\UserType;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Enum\PaymentAccountType;

/**
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\TenantRepository")
 */
class Tenant extends User
{
    /**
     * @var string
     */
    protected $type = UserType::TETNANT;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\Invite",
     *     mappedBy="tenant"
     * )
     */
    protected $invite;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\Contract",
     *     mappedBy="tenant",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     */
    protected $contracts;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\PaymentAccount",
     *     mappedBy="user",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    protected $paymentAccounts;

    /**
     * @ORM\OneToMany(
     *     targetEntity="RentJeeves\DataBundle\Entity\ResidentMapping",
     *     mappedBy="tenant",
     *     cascade={
     *         "persist",
     *         "remove",
     *         "merge"
     *     },
     *     orphanRemoval=true
     * )
     *
     * @var ArrayCollection
     */
    protected $residentsMapping;

    /**
     * @ORM\OneToOne(
     *     targetEntity="RentJeeves\DataBundle\Entity\AciImportProfileMap",
     *     mappedBy="user"
     * )
     *
     * @var AciImportProfileMap
     */
    protected $aciImportProfileMap;

    public function __construct()
    {
        parent::__construct();
        $this->contracts = new ArrayCollection();
        $this->residentsMapping = new ArrayCollection();
        $this->paymentAccounts = new ArrayCollection();
    }

    /**
     * @param ResidentMapping $resident
     */
    public function addResidentsMapping(ResidentMapping $resident)
    {
        $this->residentsMapping[] = $resident;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|ResidentMapping[]
     */
    public function getResidentsMapping()
    {
        return $this->residentsMapping;
    }

    /**
     * Set invite
     *
     * @param \RentJeeves\DataBundle\Entity\Invite $invite
     * @return \RentJeeves\DataBundle\Entity\Tenant
     */
    public function setInvite(\RentJeeves\DataBundle\Entity\Invite $invite = null)
    {
        $this->invite = $invite;

        return $this;
    }

    /**
     * Get invite
     *
     * @return \RentJeeves\DataBundle\Entity\Invite
     */
    public function getInvite()
    {
        return $this->invite;
    }

    public function getTenant()
    {
        return $this;
    }

    /**
     * Add Contract
     *
     * @param Contract $contract
     * @return \RentJeeves\DataBundle\Entity\Tenant
     */
    public function addContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove Contract
     *
     * @param Contract
     */
    public function removeContract(\RentJeeves\DataBundle\Entity\Contract $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection|Contract[]
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * @return Contract[]
     */
    public function getActiveContracts()
    {
        $result = array();
        $contracts = $this->getContracts();

        foreach ($contracts as $contract) {
            if (in_array($contract->getStatus(), array(ContractStatus::FINISHED, ContractStatus::DELETED))) {
                continue;
            }
            $result[] = $contract;
        }

        return $result;
    }

    public function getItem()
    {
        $result = array();
        $result['status'] = 'current';//$this->getContracts()->count();
        $result['name'] = $this->getFullName();
        $result['email'] = $this->getEmail();
        $result['phone'] = $this->getFormattedPhone();

        return $result;
    }

    /**
     * Add account
     *
     * @param \RentJeeves\DataBundle\Entity\PaymentAccount $account
     * @return \RentJeeves\DataBundle\Entity\Landlord
     */
    public function addPaymentAccount(\RentJeeves\DataBundle\Entity\PaymentAccount $account)
    {
        $this->paymentAccounts[] = $account;

        return $this;
    }

    /**
     * Remove account
     *
     * @param \RentJeeves\DataBundle\Entity\PaymentAccount $account
     */
    public function removePaymentAccount(\RentJeeves\DataBundle\Entity\PaymentAccount $account)
    {
        $this->paymentAccounts->removeElement($account);
    }

    /**
     * Get paymentAccounts
     *
     * @return \Doctrine\Common\Collections\Collection|PaymentAccount[]
     */
    public function getPaymentAccounts()
    {
        return $this->paymentAccounts;
    }

    /**
     * @param string|array $paymentProcessor
     * @param bool $isDisabledCreditCard
     * @return \Doctrine\Common\Collections\Collection|PaymentAccount[]
     */
    public function getPaymentAccountsByPaymentProcessor($paymentProcessor, $isDisabledCreditCard = false)
    {
        if (is_scalar($paymentProcessor)) {
            $paymentProcessor = [$paymentProcessor];
        }

        return $this->getPaymentAccounts()->filter(
            function (PaymentAccount $paymentAccount) use ($paymentProcessor, $isDisabledCreditCard) {
                if ($isDisabledCreditCard && $paymentAccount->getType() === PaymentAccountType::CARD) {
                    return false;
                }

                return in_array($paymentAccount->getPaymentProcessor(), $paymentProcessor);
            }
        );
    }

    public function getAvailableVerificationStatuses()
    {
        return UserIsVerified::all();
    }

    public function hasResident(Holding $holding, $residentId)
    {
        $residentsMapping = $this->getResidentsMapping();
        /**
         * @var $residentMapping ResidentMapping
         */
        foreach ($residentsMapping as $residentMapping) {
            if ($residentMapping->getResidentId() ===  $residentId
                && $residentMapping->getHolding()->getId() === $holding->getId()
            ) {
                return true;
            }
        }

        return false;
    }

    public function getResidentForHolding(Holding $holding)
    {
        $residentsMapping = $this->getResidentsMapping();
        /**
         * @var $residentMapping ResidentMapping
         */
        foreach ($residentsMapping as $residentMapping) {
            if ($residentMapping->getHolding()->getId() === $holding->getId()) {
                return $residentMapping;
            }
        }

        return null;
    }

    /**
     * @return AciImportProfileMap|null
     */
    public function getAciImportProfileMap()
    {
        return $this->aciImportProfileMap;
    }

    /**
     * @param AciImportProfileMap $aciImportProfileMap
     */
    public function setAciImportProfileMap(AciImportProfileMap $aciImportProfileMap)
    {
        $this->aciImportProfileMap = $aciImportProfileMap;
    }
}
