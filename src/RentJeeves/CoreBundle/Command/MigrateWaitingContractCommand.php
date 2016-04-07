<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\ContractManagement\ContractManager;
use RentJeeves\CoreBundle\ContractManagement\Model\ContractDTO;
use RentJeeves\CoreBundle\Exception\ContractManagerException;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateWaitingContractCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('renttrack:contract:migrate-waiting-contracts')
            ->setDescription('Migrate Contract Waiting to Contract, User, ResidentMapping');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ContractManager $contractManager */
        $contractManager = $this->getContainer()->get('renttrack.contract_manager');

        $query = $this->getEntityManager()
            ->createQuery('SELECT c FROM RentJeeves\DataBundle\Entity\ContractWaiting as c');
        $iterable = $query->iterate();
        $i = 0;
        while (($contractsWaitingArray = $iterable->next()) !== false) {
            $i++;
            /** @var ContractWaiting $contractWaiting */
            $contractWaiting =  reset($contractsWaitingArray);

            if ($this->isDuplicate($contractWaiting)) {
                continue;
            }

            $contractDTO = $this->mapContractWaitingToContractDTO($contractWaiting);

            try {
                $contractManager->createContract($contractWaiting->getUnit(), $contractDTO);
            } catch (ContractManagerException $e) {
                $this->getLogger()->alert(
                    sprintf(
                        'Got exception by command %s message %s',
                        MigrateWaitingContractCommand::class,
                        $e->getMessage()
                    )
                );
            }

            if ($i % 30 == 0) {
                $this->getLogger()->debug('Clear entity manager on migrate waiting contract command');
                $this->getEntityManager()->clear();
            }
        }
    }

    /**
     * @param ContractWaiting $contractWaiting
     * @return ContractDTO
     */
    protected function mapContractWaitingToContractDTO(ContractWaiting $contractWaiting)
    {
        $contractDTO = new ContractDTO();
        $contractDTO->setExternalLeaseId($contractWaiting->getExternalLeaseId());
        $contractDTO->setFirstName($contractWaiting->getFirstName());
        $contractDTO->setLastName($contractWaiting->getLastName());
        $contractDTO->setIntegratedBalance($contractWaiting->getIntegratedBalance());
        $contractDTO->setExternalResidentId($contractWaiting->getResidentId());
        $contractDTO->setRent($contractWaiting->getRent());
        $contractDTO->setPaymentAccepted($contractWaiting->getPaymentAccepted());
        $contractDTO->setStartAt($contractWaiting->getStartAt());
        $contractDTO->setFinishAt($contractWaiting->getFinishAt());

        return $contractDTO;
    }

    /**
     * @param ContractWaiting $contractWaiting
     */
    protected function isDuplicate(ContractWaiting $contractWaiting)
    {
        $contracts = $this->getEntityManager()->getRepository('RjDataBundle:Contract')
            ->findDuplicateContractPerContractWaiting($contractWaiting);

        if (empty($contracts)) {
            return false;
        }

        $this->getLogger()->debug(
            print_r(
                'ContractWaiting#%s has duplicate contracts#%s',
                $contractWaiting->getId(),
                implode(',', $contracts)
            )
        );

        return true;
    }
}
