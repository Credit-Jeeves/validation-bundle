<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper;

use RentJeeves\DataBundle\Enum\AccountingSystem;

/**
 * DI\Service('accounting_system.integration.data_mapper_factory')
 */
class DataMapperFactory
{
    /**
     * @var DataMapperInterface[]
     */
    protected $dataMappers;

    /**
     * @param array $dataMappers
     */
    public function __construct(array $dataMappers)
    {
        $this->dataMappers = $dataMappers;
    }

    /**
     * @param $accountingSystem
     * @return DataMapperInterface
     */
    public function getMapper($accountingSystem)
    {
        $accountingSystem = array_search($accountingSystem, AccountingSystem::$paymentHostIntegrated);
        if ($accountingSystem === false) {
            throw new \InvalidArgumentException('Accounting system type is invalid.');
        }

        if (!isset($this->dataMappers[$accountingSystem]) ||
            !$this->dataMappers[$accountingSystem] instanceof DataMapperInterface
        ) {
            throw new \RuntimeException(
                sprintf(
                    'Accounting system "%s" does not support mapping payment integration data.',
                    $accountingSystem
                )
            );
        }

        return $this->dataMappers[$accountingSystem];
    }
}
