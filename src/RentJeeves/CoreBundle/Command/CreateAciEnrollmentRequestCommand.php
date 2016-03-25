<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\PaymentProcessorMigration\CsvExporter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAciEnrollmentRequestCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('payment-processor:aci-import:create-enrollment-request')
            ->setDescription('Create Batch enrollment request file')
            ->addOption('holding_id', null, InputOption::VALUE_OPTIONAL, '', null)
            ->addOption('holding_id_end', null, InputOption::VALUE_OPTIONAL, '', null)
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Directory to place the output files', null)
            ->addOption('profiles', null, InputOption::VALUE_REQUIRED, 'Count profiles for 1 output file', 1000)
            ->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'The output filename prefix', null);
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

        $holdingId = $input->getOption('holding_id');
        if ($holdingId !== null) {
            $holdings = $this->getHoldings($holdingId, $input->getOption('holding_id_end'));
        }
        $exporter = $this->getCsvExporter();
        $exporter->export($input->getOption('path'), $prefix, $input->getOption('profiles'), $holdings);

        foreach ($exporter->getErrors() as $key => $errors) {
            $output->writeln(sprintf('Errors for aci profile map with id#%d:', $key));
            foreach ($errors as $error) {
                $output->writeln(sprintf('<error>%s</error>', $error));
            }
            $output->writeln('');
        }
    }

    /**
     * @param int $firstHoldingId
     * @param int $lastHoldingId
     *
     * @return array|null
     */
    protected function getHoldings($firstHoldingId, $lastHoldingId = null)
    {
        if (null === $lastHoldingId) {
            if (null === $holding = $this->getHoldingRepository()->find($firstHoldingId)) {
                throw new \InvalidArgumentException(sprintf('Holding with id#%d not found', $firstHoldingId));
            }

            return [$holding];
        } else {
            $holdings = $this->getHoldingRepository()->findHoldingsByRangeIds($firstHoldingId, $lastHoldingId);

            return empty($holdings) ? null : $holdings;
        }
    }

    /**
     * @return CsvExporter
     */
    protected function getCsvExporter()
    {
        return $this->getContainer()->get('aci_profiles_exporter');
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\HoldingRepository
     */
    protected function getHoldingRepository()
    {
        return $this->getEntityManager()->getRepository('DataBundle:Holding');
    }
}
