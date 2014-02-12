<?php
namespace RentJeeves\CheckoutBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CollectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('payment:collect')
            ->setDescription('Start collect payments');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start:');
        $jobs = $this->getContainer()->get('doctrine')->getRepository('RjDataBundle:Payment')->collectToJobs();
        $output->writeln(sprintf('%d payments added to queue', count($jobs)));
        $output->writeln('OK');
    }
}
