<?php

namespace RentJeeves\ExternalApiBundle\Services\Yardi\Soap;

use JMS\Serializer\Annotation as Serializer;
use DateTime;

class OtherOccupant
{
    /**
     * @Serializer\SerializedName("Code")
     * @Serializer\XmlAttribute
     * @Serializer\Type("string")
     */
    protected $residentId;

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
     * @Serializer\SerializedName("MoveInDate")
     * @Serializer\Type("string")
     */
    protected $moveInDate;

    /**
     * @Serializer\SerializedName("MoveOutDate")
     * @Serializer\Type("string")
     */
    protected $moveOutDate;

    /**
     * @param bool $returnObject
     * @return DateTime
     */
    public function getMoveInDate($returnObject = false)
    {
        if (!empty($this->moveInDate) && is_string($this->moveOutDate) && $returnObject) {
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
     * @return DateTime
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
    public function getResidentId()
    {
        return $this->residentId;
    }

    /**
     * @param string $residentId
     */
    public function setResidentId($residentId)
    {
        $this->residentId = $residentId;
    }
}
