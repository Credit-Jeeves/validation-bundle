<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;

interface ContractSynchronizerInterface
{
    /**
     * Execute synchronization balance
     */
    public function syncBalance();

    /**
     * Execute synchronization balance for specified holding and external property id
     *
     * @param Holding $holding
     * @param $externalPropertyId
     */
    public function syncBalanceForHoldingAndExternalPropertyId(Holding $holding, $externalPropertyId);

    /**
     * Execute synchronization rent
     */
    public function syncRent();

    /**
     * Execute synchronization rent for specified holding and external property id
     *
     * @param Holding $holding
     * @param $externalPropertyId
     */
    public function syncRentForHoldingAndExternalPropertyId(Holding $holding, $externalPropertyId);
}
