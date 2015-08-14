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

        $importer = $this->getCsvExporter();
        $importer->export($input->getOption('path'), $holding);

        foreach ($importer->getErrors() as $key => $errors) {
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

    /**
     * @return \CreditJeeves\DataBundle\Entity\HoldingRepository
     */
    protected function getHoldingRepository()
    {
        return $this->getEntityManager()->getRepository('DataBundle:Holding');
    }
}
