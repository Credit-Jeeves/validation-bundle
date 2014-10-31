<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

interface HandlerInterface
{
    public function saveForms(array $data);
}
