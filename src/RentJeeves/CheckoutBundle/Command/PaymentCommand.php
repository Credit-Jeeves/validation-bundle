<?php
namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('Payment:process')
        ->setDescription('Start auto payments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Contract');
        $contracts = $repo->getContractsForPayment();
        foreach ($contracts as $contract) {
            if ($payment = $contract->checkForPayment()) {
                // here will be payment process
                $output->writeln($contract->getStatus());
            }
        }
    }
}
