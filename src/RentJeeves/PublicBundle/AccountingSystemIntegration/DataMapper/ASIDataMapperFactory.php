<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper;

use RentJeeves\DataBundle\Enum\AccountingSystem;

/**
 * DI\Service('accounting_system.integration.data_mapper_factory')
 */
class ASIDataMapperFactory
{
    /**
     * @var ASIDataMapperInterface[]
     */
    protected $ASIDataMappers;

    /**
     * @param array $ASIDataMappers
     */
    public function __construct(array $ASIDataMappers)
    {
        $this->ASIDataMappers = $ASIDataMappers;
    }

    /**
     * @param $accountingSystem
     * @return ASIDataMapperInterface
     */
    public function getMapper($accountingSystem)
    {
        $accountingSystem = array_search($accountingSystem, AccountingSystem::$paymentHostIntegrated);
        if ($accountingSystem === false) {
            throw new \InvalidArgumentException('Accounting system type is invalid.');
        }

        if (!isset($this->ASIDataMappers[$accountingSystem]) ||
            !$this->ASIDataMappers[$accountingSystem] instanceof ASIDataMapperInterface
        ) {
            throw new \RuntimeException(
                sprintf(
                    'Accounting system "%s" does not support mapping payment integration data.',
                    $accountingSystem
                )
            );
        }

        return $this->ASIDataMappers[$accountingSystem];
    }
}
