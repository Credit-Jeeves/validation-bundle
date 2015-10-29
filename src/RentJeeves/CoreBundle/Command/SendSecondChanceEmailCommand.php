<?php
namespace RentJeeves\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendSecondChanceEmailCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('email:second_chance')
            ->setDescription('Send email to people, which we want invite but who did not make a payment');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getEm();
        $data = $em->getRepository('RjDataBundle:Contract')->findContractsForSecondChance();

        if (empty($data)) {
            $output->writeln('Contracts not found.');

            return;
        }

        foreach ($data as $contract) {
            $result = $this->getMailer()->sendSecondChanceForContract($contract);
            $output->writeln(sprintf(
                'Email for Tenant#%d with Contract#%d %ssent.',
                $contract->getTenant()->getId(),
                $contract->getId(),
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
