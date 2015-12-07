<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;

class ContractSynchronizerFactory
{
    /**
     * @var ContractSynchronizerInterface[]
     */
    protected $contractSynchronizers;

    /**
     * @param $contractSynchronizers
     */
    public function __construct(array $contractSynchronizers)
    {
        $this->contractSynchronizers = $contractSynchronizers;
    }

    /**
     * @param Holding $holding
     * @return ContractSynchronizerInterface
     */
    public function getSynchronizerByHolding(Holding $holding)
    {
        if (ApiIntegrationType::NONE === $holding->getApiIntegrationType()) {
            throw new \LogicException('Accounting system should be set up for holding.');
        }

        return $this->getSynchronizer($holding->getApiIntegrationType());
    }

    /**
     * @param string $accountingSystem
     * @see ApiIntegrationType
     * @return ContractSynchronizerInterface
     */
    public function getSynchronizer($accountingSystem)
    {
        ApiIntegrationType::throwsInvalid($accountingSystem);

        if (!isset($this->contractSynchronizers[$accountingSystem])) {
            throw new \RuntimeException(
                sprintf(
                    'Accounting system "%s" does not support synchronization.',
                    $accountingSystem
                )
            );
        }

        return $this->contractSynchronizers[$accountingSystem];
    }
}
