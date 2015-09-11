<?php
namespace RentJeeves\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendChurnRecaptureEmailCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('email:churn_recapture')
            ->setDescription('run once per month, and look for people who did not pay the previous month,
             but did pay two months before (and who still have active leases)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getEm();
        $data = $em->getRepository('DataBundle:Order')->findOrdersForChurnRecapture();

        if (empty($data)) {
            $output->writeln('Contracts not found.');

            return;
        }

        foreach ($data as $order) {
            $result = $this->getMailer()->sendChurnRecaptureForOrder($order);
            $output->writeln(sprintf(
                'Email for User#%d and Order#%d %ssent.',
                $order->getUser()->getId(),
                $order->getId(),
                $result === false ? 'not ' : ''
            ));
        }
    }

    /**
     * @return \RentJeeves\CoreBundle\Mailer\Mailer
     */
    protected function getMailer()
    {
        return $this->getContainer()->get('project.mailer');
    }

    /**
     * @return EntityManager
     */
    protected function getEm()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
