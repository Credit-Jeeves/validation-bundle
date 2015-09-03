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

        foreach ($data as $contract) {
            $this->getMailer()->sendRjTenantSecondChance($contract);
        }

        echo 1;
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
