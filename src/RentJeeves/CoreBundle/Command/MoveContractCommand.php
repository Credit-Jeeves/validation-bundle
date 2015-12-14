<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\Exception\ContractMovementManagerException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MoveContractCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:contract:move')
            ->setDescription('Move contracts from one unit to another')
            ->addOption('contract-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('dst-unit-id', null, InputOption::VALUE_REQUIRED)
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, '', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->info('Start moving.');

        if (null === $contract = $this->getContractRepository()->find($input->getOption('contract-id'))) {
            throw new \InvalidArgumentException(
                sprintf('Contract with id = %d not found.', $input->getOption('contract-id'))
            );
        }

        if (null === $dstUnit = $this->getUnitRepository()->find($input->getOption('dst-unit-id'))) {
            throw new \InvalidArgumentException(
                sprintf('Unit with id = %d not found.', $input->getOption('dst-unit-id'))
            );
        }

        if ($contract->getUnit() === $dstUnit) {
            throw new \LogicException(
                sprintf(
                    'Contract#%d already associated with Unit#%d.',
                    $input->getOption('contract-id'),
                    $input->getOption('dst-unit-id')
                )
            );
        }

        $contractMovement = $this->getContractMovementManager();
        $contractMovement->setDryRunMode($input->getOption('dry-run'));

        try {
            $contractMovement->move($contract, $dstUnit);
        } catch (ContractMovementManagerException $e) {
            $this->getLogger()->warning('Contract is not updated: ' . $e);
        }

        $this->getLogger()->info('Contract is updated.');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\UnitRepository
     */
    protected function getUnitRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:Unit');
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\ContractRepository
     */
    protected function getContractRepository()
    {
        return $this->getEntityManager()->getRepository('RjDataBundle:Contract');
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\ContractMovementManager
     */
    protected function getContractMovementManager()
    {
        return $this->getContainer()->get('dedupe.contract_movement');
    }
}
