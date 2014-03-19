<?php

namespace RentJeeves\CoreBundle\Report;

use JMS\Serializer\Annotation as Serializer;
use DateTime;

class TransUnionReportHeader
{
    protected $recordLength = '0426';                                   // 4
    protected $recordIdentifier = 'HEADER';                             // 6
    protected $reserved1 = '  ';                                        // 2
    protected $reserved2 = '          ';                                // 10
    protected $reserved3 = '          ';                                // 10
    protected $reserved4 = '     ';                                     // 5
    protected $transUnionProgramIdentifier = 'RENTTRACK ';              // 10
    protected $effectiveActivityDate;                                   // 8
    /** @Serializer\Accessor(getter="getDateCreated") */
    protected $dateCreated;                                             // 8
    protected $programDate = '        ';                                // 8
    protected $reserved5 = '00000000';                                  // 8
    /** @Serializer\Accessor(getter="getPropertyManagementName") */
    protected $propertyManagementName;                                  // 40
    /** @Serializer\Accessor(getter="getPropertyManagementAddress") */
    protected $propertyManagementAddress;                               // 96
    protected $propertyManagementPhoneNumber = '8618419090';            // 10
    /** @Serializer\Accessor(getter="getReserved6") */
    protected $reserved6;                                               // 201

    public function setActivityDate(DateTime $date)
    {
        $this->effectiveActivityDate = $date->format('mdY');
    }

    public function getDateCreated()
    {
        $today = new DateTime();

        return $today->format('mdY');
    }

    public function getPropertyManagementName()
    {
        return str_pad('RENTTRACK', 40);
    }

    public function getPropertyManagementAddress()
    {
        return str_pad('13911 RIDGEDALE DR # 401C MINNETONKA MN 55305', 96);
    }

    public function getReserved6()
    {
        return str_repeat(' ', 201);
    }
}
