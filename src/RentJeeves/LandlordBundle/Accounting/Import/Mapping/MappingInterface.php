<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Mapping;

interface MappingInterface
{
    public function getData($start, $length);

    public function isSkipped(array $row);

    public function isNeedManualMapping();

    public function getTotalContent();
}
