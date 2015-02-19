<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CloseBatchCommand extends ContainerAwareCommand
{
    protected $availableTypes = [
        ApiIntegrationType::RESMAN
    ];

    protected function configure()
    {
        $this
            ->setName('api:accounting:close-payment-batches')
            ->addOption(
                'type',
                null,
                InputOption::VALUE_OPTIONAL,
                'Accounting Package type like "resman"'
            )
            ->addOption(
                'debug',
                false,
                InputOption::VALUE_OPTIONAL,
                'Enable debug information.'
            )
            ->setDescription(
                'Send request for close all opened batches.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accountingType = $input->getOption('type');

        $types = $this->availableTypes;

        if ($accountingType && !empty($this->availableTypes[$accountingType])) {
            $types = [$accountingType];
        }

        $debug = filter_var($input->getOption('debug'), FILTER_VALIDATE_BOOLEAN);

        /** @var AccountingPaymentSynchronizer $accountingSync */
        $accountingSync = $this->getContainer()->get('accounting.payment_sync')->setDebug($debug);

        foreach ($types as $type) {
            $accountingSync->closeBatches($type);
        }
    }
}
