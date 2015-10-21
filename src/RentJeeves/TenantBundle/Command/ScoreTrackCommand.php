<?php
namespace RentJeeves\TenantBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScoreTrackCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('score-track:collect-payments')
            ->setDescription('Start collect Score Track payments')
            ->setHelp('This command must be run only once par day!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start:');
        $jobs = $this->getContainer()
            ->get('doctrine')
            ->getRepository('RjDataBundle:PaymentAccount')
            ->collectCreditTrackToJobs();
        $output->writeln(sprintf('%d payments added to queue', count($jobs)));
        $output->writeln('OK');
    }
}
