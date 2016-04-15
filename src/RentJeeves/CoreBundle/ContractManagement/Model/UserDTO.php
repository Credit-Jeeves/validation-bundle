<?php

namespace RentJeeves\CoreBundle\ContractManagement\Model;

class UserDTO
{
    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $externalResidentId;

    /**
     * @var boolean
     */
    protected $isSupportResidentId;

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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @return boolean
     */
    public function isSupportResidentId()
    {
        return $this->isSupportResidentId;
    }

    /**
     * @param boolean $isSupportResidentId
     */
    public function setIsSupportResidentId($isSupportResidentId)
    {
        $this->isSupportResidentId = $isSupportResidentId;
    }
}
