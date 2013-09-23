<?php
namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\CoreBundle\Traits\DateCommon;

class PaymentCommand extends ContainerAwareCommand
{
    use DateCommon;

    protected function configure()
    {
        $this
            ->setName('Payment:process')
            ->setDescription('Start auto payments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();
        $days = $this->getDueDays();
        $repo = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment');
        $payments = $repo->getActivePayments($days, $date->format('n'), $date->format('Y'));
        foreach ($payments as $payment) {
            //here will be payment process
            $contract = $payment->getContract();
            $tenant = $contract->getTenant();
            //dummy output he will be payment method
            $output->writeln($tenant->getFullname());
        }
    }
}
