<?php
namespace RentJeeves\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Contract;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\CoreBundle\Traits\DateCommon;
use RentJeeves\CoreBundle\DateTime;

class ContractBalanceCommand extends ContainerAwareCommand
{
    use DateCommon;

    protected function configure()
    {
        $this
            ->setName('contract:update:balance')
            ->setDescription('Update balance for contract for today as dueDate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $contractRepository = $doctrine->getRepository('RjDataBundle:Contract');
        $dueDays = $this->getDueDays();
        $contracts = $contractRepository->getContractsForUpdateBalance($dueDays);
        /**
         * @var $manager EntityManager
         */
        $manager = $doctrine->getManager();

        foreach ($contracts as $row) {
            /**
             * @var $contract Contract
             */
            $contract = end($row);
            $balance = $contract->getBalance() + $contract->getRent();
            $contract->setBalance($balance);
            $manager->persist($contract);
            $manager->flush();
            $manager->detach($contract);
        }
    }
}
