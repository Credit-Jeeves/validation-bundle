<?php

namespace RentJeeves\CoreBundle\Command;

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
            ->addOption('contract_id', null, InputOption::VALUE_REQUIRED)
            ->addOption('dst_unit_id', null, InputOption::VALUE_REQUIRED)
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, '', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->info('Start moving.');

        if (null === $contract = $this->getContractRepository()->find($input->getOption('contract_id'))) {
            throw new \InvalidArgumentException(
                sprintf('Contract with id = %s not found.', $input->getOption('contract_id'))
            );
        }

        if (null === $dstUnit = $this->getUnitRepository()->find($input->getOption('dst_unit_id'))) {
            throw new \InvalidArgumentException(
                sprintf('Unit with id = %s not found.', $input->getOption('dst_unit_id'))
            );
        }

        if ($contract->getUnit() === $dstUnit) {
            throw new \LogicException(
                sprintf('Contract already associated with Unit.')
            );
        }

        $contractMovement = $this->getContractMovement();
        $contractMovement->setDryRunMode($input->getOption('dry-run'));
        if (true === $this->getContractMovement()->move($contract, $dstUnit)) {
            $this->getLogger()->info('Contract is updated.');
        } else {
            $this->getLogger()->info('Contract is not updated.');
        }
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
     * @return \RentJeeves\CoreBundle\Services\Deduplication\ContractMovement
     */
    protected function getContractMovement()
    {
        return $this->getContainer()->get('dedupe.contract_movement');
    }
}
