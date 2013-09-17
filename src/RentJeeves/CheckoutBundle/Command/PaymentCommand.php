<?php
namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\DataBundle\Enum\PaymentType;

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
        $date = new \DateTime();
        $days = $this->getDueDays();
        $repo = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment');
        $payments = $repo->getActivePayments($days, $date->format('n'), $date->format('Y'));
        foreach ($payments as $payment) {
            //here will be payment process
            $contract = $payment->getContract();
            if ($processing = $contract->checkPaidTo()) {
                $tenant = $contract->getTenant();
                //dummy output he will be payment method
                $output->writeln($tenant->getFullname());
            }
        }
    }

    private function getDueDays()
    {
        $date = new \DateTime();
        $total = $date->format('t');
        $day = $date->format('d');
        if ($day > 27 ) {
            switch ($total) {
                case 28:
                    return array(28, 29, 30, 31);
                    break;
                case 29:
                    return array(29, 30, 31);
                    break;
                case 30:
                    return array(30, 31);
                    break;
                default:
                    return array($day);
                    break;
            }
        } else {
            return array($day);
        }
    }
}
