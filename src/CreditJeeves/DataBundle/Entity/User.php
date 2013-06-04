<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Model\User as BaseUser;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\True;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\UserRepository")
 * @ORM\Table(name="cj_user")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    /**
     * @ORM\PreRemove
     */
    public function methodPreRemove()
    {
    }

    /**
     * @ORM\PostRemove
     */
    public function methodPostRemove()
    {
    }

    /**
     * @ORM\PrePersist
     */
    public function methodPrePersist()
    {
        $this->enabled = 1;
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->setInviteCode(strtoupper(base_convert(uniqid(), 16, 36)));
    }

    /**
     * @ORM\PostPersist
     */
    public function methodPostPersist()
    {
    }

    /**
     * @ORM\PreUpdate
     */
    public function methodPreUpdate()
    {
        $this->updated_at = new \DateTime();
    }

    /**
     * @ORM\PostUpdate
     */
    public function methodPostUpdate()
    {
    }

    /**
     * @ORM\PostLoad
     */
    public function methodPostLoad()
    {
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

    public function removeData()
    {
        $this->setFirstName('');
        $this->setMiddleInitial('');
        $this->setLastName('');
        $this->setHasData(false);
        $this->setHasReport(false);
        $this->setIsActive(false);
        $this->setSsn('');
        $this->setUnitNo('');
        $this->setCity('');
        $this->setState('');
        $this->setZip('');
        $this->setPhone('');
        $this->setScoreChangedNotification(false);
        $this->setOfferNotification(false);

        return $this;
    }

    private function formatPhoneOutput($phone)
    {
        $sPhoneNumber = $this->getPhone();
        // remove all empty spaces and not number signs
        $sPhoneNumber = preg_replace('/\s+/', '', $sPhoneNumber);
        $sPhoneNumber = str_replace(array('(', ')', '-'), '', $sPhoneNumber);
        //format phone number
        $sPhoneNumber = strrev($sPhoneNumber);
        $sCityCode = substr($sPhoneNumber, 7);
        $sPhoneNumber = substr($sPhoneNumber, 0, 4) . '-' . substr($sPhoneNumber, 4, 3);
        if (!empty($sCityCode)) {
            $sPhoneNumber .= ' )' . $sCityCode . '(';
        }

        return strrev($sPhoneNumber);

    }

    /**
     * {@inheritdoc}
     */
    public function setPhone($phone)
    {
        return parent::setPhone($this->formatPhoneInput($phone));
    }

    private function formatPhoneInput($phone)
    {
        $phone = trim($phone);
        $phone = preg_replace(
            array(
                '/\s+/',
                '/\(/',
                '/\)/',
                '/-/'
            ),
            '',
            $phone
        );
        return $phone;
    }

    public function isCompleteOrderExist()
    {
        $aOrders = $this->getOrders();
        foreach ($aOrders as $Order) {
            $sStatus = $Order->getStatus();
            if ($sStatus == OrderStatus::COMPLETE) {
                return true;
            }
        }

        return false;
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

    /**
     * (non-PHPdoc)
     * @see FOS\UserBundle\Model.User::setPassword()
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function copyPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Here would be logic, how we'll get actice lead. Now - simply last
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
            'street_address1',
            'street_address2',
            'city',
            'state',
            'zip',
            'ssn',
            'is_verified',
        );
        $return = array();
        foreach ($data as $key) {
            $return[$key] = $this->$key;
        }
        return $return;
    }

    public function getUserToRemove()
    {
        $User = new self();
        $User->setFirstName($this->getFirstName());
        $User->setMiddleInitial($this->getMiddleInitial());
        $User->setLastName($this->getLastName());
        $User->copyPassword($this->getPassword());
        $User->setCreatedAt($this->getCreatedAt()); // we'll store user's created date
        $User->setIsVerified(UserIsVerified::PASSED);
        $User->setEmail($this->getEmail());
        return $User;
    }
}
