<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Group as BaseGroup;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use RentJeeves\DataBundle\Entity\DepositAccount;

/**
 * @ORM\Entity
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

    public function setMerchantName($name)
    {
        if (!$this->deposit_account) {
            $this->deposit_account = new DepositAccount();
            $this->deposit_account->setGroup($this);
        }
        $this->deposit_account->setMerchantName($name);
        return $this;
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
}
