<?php

namespace RentJeeves\CoreBundle\Report\Equifax;

use JMS\Serializer\Annotation as Serializer;

class EquifaxReportHeader
{
                                                                        // Field Length
    protected $recordLength = '0426';                                   // 4
    protected $recordIdentifier = 'HEADER';                             // 6
    protected $reserved1 = '  ';                                        // 2
    protected $reserved2 = '          ';                                // 10
    protected $propertyManagementSubCode;                               // 10
    protected $reserved4 = '     ';                                     // 5
    protected $transUnionProgramIdentifier = '          ';              // 10
    protected $effectiveActivityDate;                                   // 8
    /** @Serializer\Accessor(getter="getDateCreated") */
    protected $dateCreated;                                             // 8
    protected $programDate = '        ';                                // 8
    protected $reserved5 = '00000000';                                  // 8
    protected $propertyManagementName;                                  // 40
    protected $propertyManagementAddress;                               // 96
    protected $propertyManagementPhoneNumber;                           // 10
    /** @Serializer\Accessor(getter="getReserved6") */
    protected $reserved6;                                               // 201

    /**
     * @param \DateTime $date
     */
    public function setActivityDate(\DateTime $date)
    {
        $this->effectiveActivityDate = $date->format('mdY');
    }

    /**
     * @return string
     */
    public function getDateCreated()
    {
        $today = new \DateTime();

        return $today->format('mdY');
    }

    /**
     * @param string $propertyManagementSubCode
     */
    public function setPropertyManagementSubCode($propertyManagementSubCode)
    {
        $this->propertyManagementSubCode = $propertyManagementSubCode;
    }

    /**
     * @param string $name
     */
    public function setPropertyManagementName($name)
    {
        $this->propertyManagementName = str_pad($name, 40);
    }

    /**
     * @param string $address
     */
    public function setPropertyManagementAddress($address)
    {
        $this->propertyManagementAddress = str_pad($address, 96);
    }

    /**
     * @param string $phoneNumber
     */
    public function setPropertyManagementPhone($phoneNumber)
    {
        $this->propertyManagementPhoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getReserved6()
    {
        return str_repeat(' ', 201);
    }
}
