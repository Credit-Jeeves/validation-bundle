<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\GroupRepository")
 * @ORM\Table(name="rj_group")
 */
class Group extends BaseGroup
{
    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     * @return string
     */
    public function getApiName()
    {
        return $this->getMailingAddressName();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("street_address_1")
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getApiStreetAddress1()
    {
        return $this->getStreetAddress1();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("street_address_2")
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getApiStreetAddress2()
    {
        return $this->getStreetAddress2();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("city")
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getApiCity()
    {
        return $this->getCity();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("state")
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getApiState()
    {
        return $this->getState();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("zip")
     * @Serializer\Groups({"ContractDetails"})
     * @Serializer\Type("string")
     * @return string
     */
    public function getApiZip()
    {
        return $this->getZip();
    }

    /**
     * @return array
     */
    public function getAddressArray()
    {
        $aResult = array();
        $aAddress = array();
        $address1 = $this->getStreetAddress1();
        if (!empty($address1)) {
            $aAddress[] = $address1;
        }
        $address2 = $this->getStreetAddress2();
        if (!empty($address2)) {
            $aAddress[] = $address2;
        }
        $aResult[] = implode(' ', $aAddress);
        $aAddress = array();
        $city = $this->getCity();
        $state = $this->getState();
        $zip = $this->getZip();
        if (!empty($city)) {
            if (!empty($state) || !empty($zip)) {
                $aAddress[] = $city.',';
            } else {
                $aAddress[] = $city;
            }
        }
        if (!empty($state)) {
            $aAddress[] = $state;
        }
        if (!empty($zip)) {
            $aAddress[] = $zip;
        }
        $aResult[] = implode(' ', $aAddress);

        return $aResult;
    }

    public function getCountLeads()
    {
        $leads = $this->getLeads();

        return $leads ? count($leads) : 0;
    }

    public function __toString()
    {
        return $this->getName() ? $this->getName() : 'New';
    }

    public function getCountProperties()
    {
        $properties = $this->getGroupProperties();

        return $properties ? count($properties) : 0;
    }

    public function getMainDealer()
    {
        $dealer = $this->getDealer();
        if ($dealer) {
            return $dealer;
        }
        $dealers = $this->getGroupDealers();
        foreach ($dealers as $dealer) {
            $isAdmin = $dealer->isSuperAdmin();
            if ($isAdmin) {
                return $dealer;
            }
        }

        return $dealer;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBillingAccountsByCurrentPaymentProcessor()
    {
        $currentPaymentProcessor = $this->getGroupSettings()->getPaymentProcessor();

        return $this->getBillingAccounts()->filter(
            function ($entry) use ($currentPaymentProcessor) {
                return $currentPaymentProcessor === $entry->getPaymentProcessor();
            }
        );
    }

    /**
     * @return BillingAccount|null
     */
    public function getActiveBillingAccount()
    {
        /** @var BillingAccount $account */
        foreach ($this->getBillingAccountsByCurrentPaymentProcessor() as $account) {
            if ($account->getIsActive()) {
                return $account;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isExistGroupSettings()
    {
        return empty($this->groupSettings) ? false : true;
    }

    /**
     * @return bool
     */
    public function isExistImportSettings()
    {
        return !empty($this->importSettings);
    }

    /**
     * @return ImportGroupSettings
     */
    public function getImportSettings()
    {
        if (empty($this->importSettings)) {
            $this->importSettings = new ImportGroupSettings();
            $this->importSettings->setGroup($this);
        }

        return $this->importSettings;
    }

    /**
     * @return GroupSettings
     */
    public function getGroupSettings()
    {
        if (empty($this->groupSettings)) {
            $this->groupSettings = new GroupSettings();
            $this->groupSettings->setGroup($this);
        }

        return $this->groupSettings;
    }

    public function getID4StatementDescriptor()
    {
        return $this->getStatementDescriptor() ?: substr($this->getName(), 0, 14);
    }

    /**
     * @return string|null
     */
    public function getRentAccountNumberPerCurrentPaymentProcessor()
    {
        $depositAccount = $this->getRentDepositAccountForCurrentPaymentProcessor();
        if ($depositAccount && $depositAccount->getAccountNumber()) {
            return $depositAccount->getAccountNumber();
        }

        return null;
    }

    /**
     * @return null|SettingsInterface
     */
    public function getIntegratedApiSettings()
    {
        $holding = $this->getHolding();
        switch ($holding->getApiIntegrationType()) {
            case ApiIntegrationType::AMSI:
                return $holding->getAmsiSettings();
            case ApiIntegrationType::MRI:
                return $holding->getMriSettings();
            case ApiIntegrationType::RESMAN:
                return $holding->getResManSettings();
            case ApiIntegrationType::YARDI_VOYAGER:
                return $holding->getYardiSettings();
            case ApiIntegrationType::NONE:
            default:
                return null;
        }
    }

    /**
     * @return int|null
     */
    public function getAciCollectPayProfileId()
    {
        return $this->getAciCollectPayProfile() ? $this->getAciCollectPayProfile()->getProfileId() : null;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return 'US';
    }

    /**
     * @Assert\True(message = "admin.error.deposit_account_number", groups={"unique_mapping"})
     * @return boolean
     *
     */
    public function isValidDepositAccountUniqueIndexForAccountNumber()
    {
        $alreadyUsedDepositAccounts = [];
        foreach ($this->getDepositAccounts() as $account) {
            $accountNumber = $account->getAccountNumber();
            if (empty($accountNumber)) {
                continue;
            }
            $key = $account->getType() . $account->getPaymentProcessor() . $accountNumber . $this->getHolding()
                    ->getId();
            if (in_array($key, $alreadyUsedDepositAccounts)) {
                return false;
            }
            $alreadyUsedDepositAccounts[] = $key;
        }

        return true;
    }

    /**
     * @Assert\True(message = "admin.error.deposit_account", groups={"unique_mapping"})
     * @return boolean
     */
    public function isValidDepositAccountUniqueIndex()
    {
        $alreadyUsedDepositAccounts = [];
        foreach ($this->getDepositAccounts() as $account) {
            $key = $account->getType() . $this->getId() . $account->getPaymentProcessor();
            if (in_array($key, $alreadyUsedDepositAccounts)) {
                return false;
            }

            $alreadyUsedDepositAccounts[] = $key;
        }

        return true;
    }

    /**
     * @Assert\True(message = "error.statement_descriptor.too_long", groups={"holding"})
     * @return boolean
     */
    public function isValidDescriptor()
    {
        if ($this->getGroupSettings()->getPaymentProcessor() === PaymentProcessor::HEARTLAND) {
            $limit = 14;
        } else {
            $limit = 21;
        }

        $length = strlen($this->getStatementDescriptor());

        return $length <= $limit;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|DepositAccount[]
     */
    public function getNotRentDepositAccountsForCurrentPaymentProcessor()
    {
        return $this->getDepositAccounts()->filter(function (DepositAccount $account) {
            return DepositAccountType::RENT !== $account->getType() &&
                $account->getPaymentProcessor() === $this->getGroupSettings()->getPaymentProcessor();
        });
    }

    /**
     * @param string $type
     * @return DepositAccount|null
     */
    public function getDepositAccountForCurrentPaymentProcessor($type)
    {
        return $this->getDepositAccount($type, $this->getGroupSettings()->getPaymentProcessor());
    }
}
