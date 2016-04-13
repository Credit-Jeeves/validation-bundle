<?php

namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\ContractManagement\ContractManager;
use RentJeeves\CoreBundle\ContractManagement\Model\ContractDTO;
use RentJeeves\CoreBundle\Exception\ContractManagerException;
use RentJeeves\CoreBundle\Exception\UserCreatorException;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\UserManagement\UserCreator;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Enum\ContractStatus;
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
        $this->getLogger()->info('Started migration from ContractWaiting to Contract');
        /** @var ContractProcess $contractManager */
        $contractManager = $this->getContainer()->get('contract.process');
        $contractManager->setIsValidateContract(true);
        /** @var UserCreator $userCreator */
        $userCreator = $this->getContainer()->get('renttrack.user_creator');

        $iterableResult = $this
            ->getEntityManager()
            ->createQuery('SELECT c FROM RentJeeves\DataBundle\Entity\ContractWaiting as c')
            ->iterate();
        /** @var ContractWaiting $contractWaiting */
        while ((list($contractWaiting) = $iterableResult->next()) !== false) {
            $this->getLogger()->info(
                sprintf(
                    'Started processing ContractWaiting#%d',
                    $contractWaiting->getId()
                )
            );
            if ($this->isDuplicate($contractWaiting)) {
                continue;
            }
            try {
                $this->getLogger()->info(
                    sprintf(
                        'Trying create new waiting tenant with name: %s %s',
                        $contractWaiting->getFirstName(),
                        $contractWaiting->getLastName()
                    )
                );
                $tenant = $userCreator->createTenant($contractWaiting->getFirstName(), $contractWaiting->getLastName());
            } catch (UserCreatorException $e) {
                $this->getLogger()->warning(
                    sprintf(
                        'Got error when trying create new tenant for ContractWaiting#%d: %s',
                        $contractWaiting->getId(),
                        $e->getMessage()
                    )
                );
                continue;
            }

            $contract = $contractManager->createContractFromWaiting(
                $tenant,
                $contractWaiting,
                $contractWaiting->getGroup()->isAllowedEditResidentId()
            );

            if (!$contract) {
                $this->getLogger()->warning(
                    sprintf(
                        'Got errors when trying move Contract from ContractWaiting#%d: %s',
                        $contractWaiting->getId(),
                        implode(', ', $contractManager->getErrors())
                    )
                );
            }

            $contract->setStatus(ContractStatus::WAITING);
            $this->getEntityManager()->flush();
            $this->getLogger()->info(
                sprintf(
                    'Migrated Contract#%d from ContractWaiting#%d',
                    $contract->getId(),
                    $contractWaiting->getId()
                )
            );

            $this->getLogger()->info('Clear entity manager on migrate waiting contract command');
            $this->getEntityManager()->clear();
        }

        $this->getLogger()->info('Finished migration from ContractWaiting to Contract');
    }

    /**
     * @param ContractWaiting $contractWaiting
     * @return boolean
     */
    protected function isDuplicate(ContractWaiting $contractWaiting)
    {
        $contracts = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Contract')
            ->findDuplicateContractPerContractWaiting($contractWaiting);

        if (empty($contracts)) {
            return false;
        }

        $this->getLogger()->warning(
            sprintf(
                'ContractWaiting#%d has duplicate contracts with ids %s',
                $contractWaiting->getId(),
                implode(', ', $contracts)
            )
        );

        return true;
    }
}
