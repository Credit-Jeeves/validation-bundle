<?php
namespace RentJeeves\LandlordBundle\Tests\Unit\Import\Mapping;

use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract;

/*
 * this is only used in MappingAbstractCase unit test, but PHP_CodeSniffer
 * would not let me include it within the same file.
 */
class MappingTest extends MappingAbstract
{

    public function testStreetParse($row)
    {
        return $this->parseStreet($row);
    }

    public function testUnitParse($row)
    {
        return $this->parseUnit($row);
    }

    public function getData($start, $length)
    {
        // stub to implement interface
    }

    public function isSkipped(array $row)
    {
        // stub to implement interface
    }

    public function isNeedManualMapping()
    {
        // stub to implement interface
    }

    public function getTotalContent()
    {
        // stub to implement interface
    }
}
