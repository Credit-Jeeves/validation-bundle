<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\PaymentProcessorMigration\CsvImporter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportAciEnrollmentResponseFileCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('payment-processor:aci-import:import-enrollment-file')
            ->setDescription('Import batch enrollment response file')
            ->addOption('holding_id', null, InputOption::VALUE_OPTIONAL, '', null)
            ->addOption('path', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $holdingId = $input->getOption('holding_id');
        $holding = null;
        if ($holdingId !== null && null === $holding = $this->getHoldingRepository()->find($holdingId)) {
            throw new \InvalidArgumentException(sprintf('Holding with id#%d not found', $holdingId));
        }

        $importer = $this->getCsvImporter();
        $importer->import($input->getOption('path'), $holding);

        foreach ($importer->getErrors() as $error) {
            $output->writeln(sprintf('<error>%s</error>', $error));
        }
    }

    /**
     * @return CsvImporter
     */
    protected function getCsvImporter()
    {
        return $this->getContainer()->get('aci_profiles_importer');
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\HoldingRepository
     */
    protected function getHoldingRepository()
    {
        return $this->getEntityManager()->getRepository('DataBundle:Holding');
    }
}
