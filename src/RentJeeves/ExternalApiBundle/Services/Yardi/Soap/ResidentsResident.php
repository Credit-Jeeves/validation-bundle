<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;
use DateTime;

class ResidentsResident
{
    /**
     * @Serializer\SerializedName("Status")
     * @Serializer\Type("string")
     */
    protected $status;

    /**
     * @Serializer\SerializedName("tCode")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $code;

    /**
     * @Serializer\SerializedName("MoveOutDate")
     * @Serializer\Type("string")
     */
    protected $moveOutDate;

    /**
     * @Serializer\SerializedName("MoveInDate")
     * @Serializer\Type("string")
     */
    protected $moveInDate;

    /**
     * @Serializer\SerializedName("paymentAccepted")
     * @Serializer\Type("string")
     */
    protected $paymentAccepted;

    /**
     * @Serializer\SerializedName("leaseId")
     * @Serializer\Type("string")
     */
    protected $leaseId;

    /**
     * @Serializer\SerializedName("OtherOccupants")
     * @Serializer\Type("RentJeeves\ExternalApiBundle\Services\Yardi\Soap\OtherOccupants")
     */
    protected $otherOccupants;

    /**
     * @Serializer\SerializedName("FirstName")
     * @Serializer\Type("string")
     */
    protected $firstName;

    /**
     * @Serializer\SerializedName("LastName")
     * @Serializer\Type("string")
     */
    protected $lastName;

    /**
     * @Serializer\SerializedName("Email")
     * @Serializer\Type("string")
     */
    protected $email;

    /**
     * @Serializer\SerializedName("isRoommate")
     * @Serializer\Type("boolean")
     */
    protected $isRoommate = false;

    /**
     * @param bool $returnObject
     * @return mixed
     */
    public function getMoveInDate($returnObject = false)
    {
        if (!empty($this->moveInDate) && is_string($this->moveInDate) && $returnObject) {
            return DateTime::createFromFormat('m/d/Y', $this->moveInDate);
        }

        return $this->moveInDate;
    }

    /**
     * @param mixed $moveInDate
     */
    public function setMoveInDate($moveInDate)
    {
        $this->moveInDate = $moveInDate;
    }

    /**
     * @param bool $returnObject
     * @return mixed
     */
    public function getMoveOutDate($returnObject = false)
    {
        if (!empty($this->moveOutDate) && is_string($this->moveOutDate) && $returnObject) {
            return DateTime::createFromFormat('m/d/Y', $this->moveOutDate);
        }

        return $this->moveOutDate;
    }

    /**
     * @param mixed $moveOutDate
     */
    public function setMoveOutDate($moveOutDate)
    {
        $this->moveOutDate = $moveOutDate;

        return $this;
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
     * @return boolean
     */
    public function isRoommate()
    {
        return $this->isRoommate;
    }

    /**
     * @param boolean $isRoommate
     */
    public function setIsRoommate($isRoommate)
    {
        $this->isRoommate = $isRoommate;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return array
     */
    public function getOtherOccupants()
    {
        return $this->otherOccupants;
    }

    /**
     * @param string $otherOccupants
     */
    public function setOtherOccupants($otherOccupants)
    {
        $this->otherOccupants = $otherOccupants;
    }

    /**
     * @return string
     */
    public function getLeaseId()
    {
        return $this->leaseId;
    }

    /**
     * @param string $leaseId
     */
    public function setLeaseId($leaseId)
    {
        $this->leaseId = $leaseId;
    }

    /**
     * @return string
     */
    public function getPaymentAccepted()
    {
        return $this->paymentAccepted;
    }

    /**
     * @param string $paymentAccepted
     */
    public function setPaymentAccepted($paymentAccepted)
    {
        $this->paymentAccepted = $paymentAccepted;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getResidentId()
    {
        return $this->getCode();
    }
}
