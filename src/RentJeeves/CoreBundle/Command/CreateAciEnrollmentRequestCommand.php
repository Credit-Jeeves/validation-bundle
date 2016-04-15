<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\PaymentProcessorMigration\CsvExporter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAciEnrollmentRequestCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('payment-processor:aci-import:create-enrollment-request')
            ->setDescription('Create Batch enrollment request file')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Directory to place the output files', null)
            ->addOption('profiles', null, InputOption::VALUE_REQUIRED, 'Count profiles for 1 output file', 1000)
            ->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'The output filename prefix', null)
            ->addArgument(
                'holding_ids',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'What holdings do you want to export tokens for (separate multiple holding IDs with a space)?',
                null
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $holdings = null;
        if (null === $prefix = $input->getOption('prefix')) {
            throw new \InvalidArgumentException('Option "prefix" cannot be NULL.');
        }
        $pathToDir = $input->getOption('path');
        if (false === is_dir($pathToDir) || false === is_writable($pathToDir)) {
            throw new \InvalidArgumentException('Option "path" should contain path to writable directory.');
        }
        $holdingIds = $input->getArgument('holding_ids');
        $this->verifyHoldingsExist($holdingIds);

        $exporter = $this->getCsvExporter();
        $exporter->export($input->getOption('path'), $prefix, $input->getOption('profiles'), $holdingIds);

        foreach ($exporter->getErrors() as $key => $errors) {
            $output->writeln(sprintf('Errors for aci profile map with id#%d:', $key));
            foreach ($errors as $error) {
                $output->writeln(sprintf('<error>%s</error>', $error));
            }
            $output->writeln('');
        }
    }

    /**
     * @return CsvExporter
     */
    protected function getCsvExporter()
    {
        return $this->getContainer()->get('aci_profiles_exporter');
    }

    protected function verifyHoldingsExist(array $holdingIds)
    {
        foreach ($holdingIds as $holdingId) {
            if (null === $this->getHoldingRepository()->find($holdingId)) {
                throw new \InvalidArgumentException(sprintf('Holding with id#%d not found', $holdingId));
            }
        }
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\HoldingRepository
     */
    protected function getHoldingRepository()
    {
        return $this->getEntityManager()->getRepository('DataBundle:Holding');
    }
}
