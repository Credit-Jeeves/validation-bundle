<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\DataBundle\Entity\BillingAccount;
use RentJeeves\DataBundle\Entity\GroupAccountNumberMapping;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Entity\DepositAccount;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\GroupRepository")
 * @ORM\Table(name="cj_account_group")
 */
class Group extends BaseGroup
{
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

    public function getMerchantName()
    {
        $depositAccount = $this->getDepositAccount();
        return !empty($depositAccount) ? $depositAccount->getMerchantName() : '';
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
     * @return BillingAccount|null
     */
    public function getActiveBillingAccount()
    {
        foreach ($this->getBillingAccounts() as $account) {
            if ($account->getIsActive()) {
                return $account;
            }
        }

        return null;
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

    /**
     * @return DepositAccount
     */
    public function getDepositAccount()
    {
        if (empty($this->depositAccount)) {
            $this->depositAccount = new DepositAccount();
            $this->depositAccount->setGroup($this);
        }

        return $this->depositAccount;
    }

    public function getID4StatementDescriptor()
    {
        return $this->getStatementDescriptor() ?: substr($this->getName(), 0, 14);
    }

    public function getAccountNumber()
    {
        if ($accountNumberMapping = $this->getAccountNumberMapping()) {
            return $accountNumberMapping->getAccountNumber();
        }

        return null;
    }

    public function setAccountNumber($accountNumber)
    {
        if (!$accountNumberMapping = $this->getAccountNumberMapping()) {
            $accountNumberMapping = new GroupAccountNumberMapping();
            $this->setAccountNumberMapping($accountNumberMapping);
        }
        $accountNumberMapping->setAccountNumber($accountNumber);
    }

    public function getAccountNumberMapping()
    {
        if (!$this->accountNumberMapping) {
            $this->accountNumberMapping = new GroupAccountNumberMapping();
            $this->accountNumberMapping->setGroup($this);
        }

        return $this->accountNumberMapping;
    }

    /**
     * @return null|SettingsInterface
     */
    public function getIntegratedApiSettings()
    {
        $holding = $this->getHolding();
        $accountingSetting = $holding->getAccountingSettings();
        $apiIntegration = $accountingSetting->getApiIntegration();

        switch ($apiIntegration) {
            case ApiIntegrationType::AMSI:
                return $holding->getAmsiSettings();
            case ApiIntegrationType::MRI:
                return $holding->getMriSettings();
            case ApiIntegrationType::RESMAN:
                return $holding->getResManSettings();
            case ApiIntegrationType::YARDI_VOYAGER:
                return $holding->getYardiSettings();
        }

        return null;
    }
}
