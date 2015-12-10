<?php

namespace RentJeeves\ExternalApiBundle\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncContractRentCommand extends BaseCommand
{
    const NAME = 'renttrack:contract:synchronize:rent';
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->addOption(
                'jms-job-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Job ID'
            )
            ->addOption(
                'holding-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Holding id in renttrack system'
            )
            ->addOption(
                'external-property-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Property id in accounting system'
            )
            ->setDescription(
                'Update contracts rent for each resident for specified holding and external property.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $externalPropertyId = $input->getOption('external-property-id');

        if (false == $input->getOption('holding-id')) {
            throw new \InvalidArgumentException('Please, specify holding id!');
        }

        if (false == $input->getOption('external-property-id')) {
            throw new \InvalidArgumentException('Please, specify external property id!');
        }

        if (false == $holding = $this->getHolding($input->getOption('holding-id'))) {
            throw new \InvalidArgumentException(
                sprintf('Holding with id #%s not found.', $input->getOption('holding-id'))
            );
        }

        $this->getContainer()->get('contract_sync.factory')
            ->getSynchronizerByHolding($holding)
            ->syncRentForHoldingAndExternalPropertyId($holding, $externalPropertyId);
    }

    /**
     * @param int $holdingId
     * @return Holding
     */
    protected function getHolding($holdingId)
    {
        return $this->getEntityManager()->find('DataBundle:Holding', $holdingId);
    }
}
