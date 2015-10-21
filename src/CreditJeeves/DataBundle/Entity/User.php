<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Enum\UserType;
use CreditJeeves\DataBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\CoreBundle\Services\PhoneNumberFormatter;
use RentJeeves\CoreBundle\Services\SocialSecurityNumberFormatter;
use RentJeeves\DataBundle\Entity\Partner;
use RentJeeves\DataBundle\Entity\PartnerUserMapping;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\DataBundle\Enum\CreditSummaryVendor;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\UserRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="UserType")
 * @ORM\DiscriminatorMap({
 *      "tenant" = "RentJeeves\DataBundle\Entity\Tenant",
 *      "landlord" = "RentJeeves\DataBundle\Entity\Landlord",
 *      "partner" = "RentJeeves\DataBundle\Entity\PartnerUser",
 *      "admin" = "Admin",
 *      "applicant" = "Applicant",
 *      "dealer" = "Dealer"
 * })
 * @ORM\Table(name="cj_user")
 * @ORM\HasLifecycleCallbacks()
 */
abstract class User extends BaseUser
{
    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
    }

    /**
     * @ORM\PostRemove
     */
    public function postRemove()
    {
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->enabled = 1;
        $this->updated_at = new DateTime();
        if (!$this->getInviteCode()) {
            $this->setInviteCode(strtoupper(base_convert(uniqid(), 16, 36)));
        }
        $this->cleanPhoneNumber();
    }

    /**
     * @ORM\PostPersist
     */
    public function postPersist()
    {
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated_at = new DateTime();
        $this->cleanPhoneNumber();
    }

    /**
     * @ORM\PostUpdate
     */
    public function postUpdate()
    {
    }

    /**
     * @ORM\PostLoad
     */
    public function postLoad()
    {
    }

    public function getRoles()
    {
        switch ($this->getType()) {
            case UserType::APPLICANT:
                return array('ROLE_USER');
            case UserType::DEALER:
                return array('ROLE_DEALER');
            case UserType::ADMIN:
                return array('ROLE_ADMIN');
            case UserType::TETNANT:
                return array('ROLE_TENANT');
            case UserType::LANDLORD:
                return array('ROLE_LANDLORD');
            case UserType::PARTNER:
                return array('ROLE_PARTNER');
        }
        throw new \RuntimeException(sprintf("Wrong type '%s'", $this->getType()));
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        $this->email = $email;
        $this->setEmailCanonical(strtolower($email));
        $this->setUsername($email);
        $this->setUsernameCanonical(strtolower($email));

        return $this;
    }

    /**
     * @param string $ssn
     * @return User
     */
    public function setSsn($ssn)
    {
        return parent::setSsn(SocialSecurityNumberFormatter::formatToDigitsOnly($ssn));
    }

    /**
     * @return string
     */
    public function getFormattedSsn()
    {
        return SocialSecurityNumberFormatter::formatWithDashes($this->getSsn());
    }

    public function getFormattedPhone()
    {
        return PhoneNumberFormatter::formatWithBracketsAndDash($this->phone);
    }

    /**
     * Used in prePersist and preUpdate to prevent adding non numeric symbols to phone numbers.
     */
    public function cleanPhoneNumber()
    {
        $this->phone = PhoneNumberFormatter::formatToDigitsOnly($this->phone);
    }

    /**
     * @return OrderSubmerchant | null
     */
    public function getLastCompleteOrder()
    {
        $return = null;
        $orders = $this->getOrders();
        /** @var OrderSubmerchant $order */
        foreach ($orders as $order) {
            if (OrderStatus::COMPLETE == $order->getStatus()) {
                $return = $order;
            }
        }

        return $return;
    }

    public function isCompleteOrderExist()
    {
        return null != $this->getLastCompleteOrder();
    }

    /**
     * @param \CreditJeeves\DataBundle\Enum\OperationType $type
     */
    public function getLastCompleteReportOperation()
    {
        $orders = array_reverse((array) $this->getOrders()->getIterator());

        /** @var OrderSubmerchant $order */
        foreach ($orders as $order) {
            if (OrderStatus::COMPLETE == $order->getStatus()) {
                /** @var Operation $operation */
                foreach ($order->getOperations() as $operation) {
                    if ($operation->getType() == OperationType::REPORT) {
                        return $operation;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function displaySsn()
    {
        $sSSN = substr($this->getSsn(), 0, 5);

        return substr($sSSN, 0, 3) . '-' . substr($sSSN, 3) . '-XXXX';
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return sprintf(
            "%s %s",
            (!empty($this->middle_name)
                ? $this->getFirstName() . ' ' . $this->getMiddleInitial() : $this->getFirstName()),
            $this->getLastName()
        );
    }

    public function copyPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Here would be logic, how we'll get active lead. Now - simply last
     *
     * @return Lead
     */
    public function getActiveLead()
    {
        $nLeads = $this->getUserLeads()->count();
        if ($nLeads > 0) {
            return $this->getUserLeads()->last();
        } else {
            return new Lead();
        }
    }

    public function getActiveGroup()
    {
        $nGroups = $this->getHolding()->getGroups()->count();
        if ($nGroups > 0) {
            return $this->getHolding()->getGroups()->first();
        } else {
            return new Group();
        }
    }

    /**
     * @return array
     */
    public function getArrayForPidkiq()
    {
        $data = array(
            'id',
            'first_name',
            'middle_initial',
            'last_name',
            'ssn',
            'is_verified',
        );
        $return = array(
            'unit' => '',
            'number' => '',
            'street' => '',
            'city' => '',
            'zip' => '',
            'country' => '',
        );
        foreach ($data as $key) {
            $return[$key] = $this->$key;
        }
        /** @var Address $address */
        if ($address = $this->getDefaultAddress()) {
            $return['unit'] = $address->getUnit();
            $return['number'] = $address->getNumber();
            $return['street'] = $address->getStreet();
            $return['city'] = $address->getCity();
            $return['zip'] = $address->getZip();
            $return['country'] = $address->getCountry();
        }

        return $return;
    }

    public function getUserToRemove()
    {
        /** @var User $User */
        $User = new static();
        $User->setId($this->getId());
        $User->setFirstName($this->getFirstName());
        $User->setMiddleInitial($this->getMiddleInitial());
        $User->setLastName($this->getLastName());
        $User->copyPassword($this->getPassword());
        $User->setCulture($this->getCulture());
        $User->setCreatedAt($this->getCreatedAt()); // we'll store user's created date
        $User->setEmail($this->getEmail());
        $User->setHasData(false);
        // TODO recheck
        $User->setIsActive(true);
        $User->setEnabled($this->enabled);
        $User->setLocked($this->locked);
        $User->setExpired($this->expired);
        $User->setCredentialsExpired($this->credentialsExpired);

        return $User;
    }

    /**
     * @return Address
     */
    public function getDefaultAddress()
    {
        $return = null;
        /** @var Address $address */
        foreach ($this->getAddresses() as $address) { // TODO find faster way
            $return = $address;
            if ($return->getIsDefault()) { // TODO add check if default not find
                break;
            }
        }

        return $return;
    }

    /**
     * @deprecated
     */
    public function getStreetAddress1()
    {
        if ($address = $this->getDefaultAddress()) {
            return $address->getStreet();
        }

        return null;
    }

    /**
     * @deprecated
     */
    public function getStreetAddress2()
    {
        if ($address = $this->getDefaultAddress()) {
            return $address->getUnit();
        }

        return null;
    }

    /**
     * @deprecated
     */
    public function getState()
    {
        if ($address = $this->getDefaultAddress()) {
            return $address->getArea();
        }

        return null;
    }

    /**
     * @deprecated
     */
    public function getZip()
    {
        if ($address = $this->getDefaultAddress()) {
            return $address->getZip();
        }

        return null;
    }

    /**
     * @deprecated
     */
    public function getCity()
    {
        if ($address = $this->getDefaultAddress()) {
            return $address->getCity();
        }

        return null;
    }

    public function getLastScore()
    {
        $score = $this->getScores()->last();

        return $score ? $score->getScore() : 0;
    }

    public function getLastFicoScore()
    {
        $score = $this->getLastScore();
        $nFicoScore = round(10 * (($score - 483.06) / 11.079) + 490);
        $nFicoScore = $nFicoScore > 850 ? 850 : $nFicoScore;

        return $score ? $nFicoScore : 0;
    }

    /**
     * @return string
     */
    public function getDOB()
    {
        $dob = parent::getDateOfBirth();

        return $dob ? $dob->format('mdY') : '';
    }

    /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner ? $this->partner->getPartner() : null;
    }

    /**
     * @param Partner $partner
     */
    public function setPartner($partner)
    {
        if ($this->getId()) {
            $this->updateUserPartner($partner);
        } else {
            $this->addUserPartner($partner);
        }
    }

    /**
     * @param Partner $partner
     */
    protected function addUserPartner(Partner $partner)
    {
        $partnerUserMapping = new PartnerUserMapping();
        $partnerUserMapping->setPartner($partner);
        $partnerUserMapping->setUser($this);
        $this->partner = $partnerUserMapping;
    }

    /**
     * @param Partner $partner
     */
    protected function updateUserPartner(Partner $partner)
    {
        $partnerUserMapping = $this->partner;
        if (!$partnerUserMapping) {
            $partnerUserMapping = new PartnerUserMapping();
            $partnerUserMapping->setUser($this);
        }
        $partnerUserMapping->setPartner($partner);
        $this->partner = $partnerUserMapping;
    }

    /**
     * @return int|null
     */
    public function getAciCollectPayProfileId()
    {
        if ($this->getAciCollectPayProfile()) {
            return $this->getAciCollectPayProfile()->getProfileId();
        }

        return null;
    }

    /**
     * @param $vendor
     * @return Report
     * @throws \Exception
     */
    public function getLastReportByVendor($vendor)
    {
        CreditSummaryVendor::throwsInvalid($vendor);

        switch ($vendor) {
            case CreditSummaryVendor::TRANSUNION:
                return $this->getReportsTUSnapshot()->last();
            case CreditSummaryVendor::EXPERIAN:
                return $this->getReportsPrequal()->last();
            default:
                throw new \Exception(sprintf('Unsupported credit summary vendor "%s"', $vendor));
        }
    }
}
