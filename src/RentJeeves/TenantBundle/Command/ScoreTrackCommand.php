<?php
namespace RentJeeves\TenantBundle\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\JobRelatedCreditTrack;
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
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $paymentAccounts = $em->getRepository('RjDataBundle:PaymentAccount')->getCreditTrackPaymentAccountForDueDays();

        /** @var PaymentAccount $paymentAccount */
        foreach ($paymentAccounts as $paymentAccount) {
            $job = new Job('payment:pay', ['--app=rj']);
            $relatedEntity = new JobRelatedCreditTrack();
            $relatedEntity->setCreditTrackPaymentAccount($paymentAccount);
            $job->addRelatedEntity($relatedEntity);
            $em->persist($job);
        }
        $em->flush();
        $output->writeln(sprintf('%d payments added to queue', count($paymentAccounts)));
        $output->writeln('OK');
    }
}
