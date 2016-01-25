<?php

namespace RentJeeves\ExternalApiBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Enum\AccountingSystem;

class ContractSynchronizerFactory
{
    /**
     * @var ContractSynchronizerInterface[]
     */
    protected $contractSynchronizers;

    /**
     * @param array $contractSynchronizers
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
        if (AccountingSystem::NONE === $holding->getAccountingSystem()) {
            throw new \LogicException('Accounting system should be set up for holding.');
        }

        if ($holding->isApiIntegrated() === false) {
            $message = sprintf('This accounting system (%s) not use api.', $holding->getAccountingSystem());

            throw new \LogicException($message);
        }

        return $this->getSynchronizer($holding->getAccountingSystem());
    }

    /**
     * @param string $accountingSystem
     * @see AccountingSystem
     * @return ContractSynchronizerInterface
     */
    public function getSynchronizer($accountingSystem)
    {
        AccountingSystem::throwsInvalid($accountingSystem);

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
