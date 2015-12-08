<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;

interface ContractSynchronizerInterface
{
    /**
     * Execute balance synchronization for all integrated holdings in the system
     *  and create jobs for synchronization each pair holding and external property.
     */
    public function syncBalance();

    /**
     * Execute balance synchronization for specified holding and external property id
     *
     * @param Holding $holding
     * @param string $externalPropertyId
     */
    public function syncBalanceForHoldingAndExternalPropertyId(Holding $holding, $externalPropertyId);

    /**
     * Execute rent synchronization for all integrated holdings in the system
     *  and create jobs for synchronization each pair holding and external property
     */
    public function syncRent();

    /**
     * Execute rent synchronization for specified holding and external property id
     *
     * @param Holding $holding
     * @param string $externalPropertyId
     */
    public function syncRentForHoldingAndExternalPropertyId(Holding $holding, $externalPropertyId);
}
