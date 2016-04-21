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
        $this->getEntityManager()->getConnection()->getConfiguration()->setSQLLogger(null);
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
            $contractWaitingId = $contractWaiting->getId();
            $this->printMemoryUsage();
            $this->getLogger()->debug(
                sprintf(
                    'Started processing ContractWaiting#%d',
                    $contractWaitingId
                )
            );
            if ($this->isDuplicate($contractWaiting)) {
                continue;
            }
            $this->getEntityManager()->beginTransaction();
            try {
                $this->getLogger()->debug(
                    sprintf(
                        'Trying create new waiting tenant with name: %s %s',
                        $contractWaiting->getFirstName(),
                        $contractWaiting->getLastName()
                    )
                );
                $tenant = $userCreator->createTenant($contractWaiting->getFirstName(), $contractWaiting->getLastName());
            } catch (UserCreatorException $e) {
                $this->getEntityManager()->rollback();
                $this->getLogger()->warning(
                    sprintf(
                        'Got error when trying create new tenant for ContractWaiting#%d: %s',
                        $contractWaitingId,
                        $e->getMessage()
                    )
                );
                continue;
            }

            try {
                $contract = $contractManager->createContractFromWaiting(
                    $tenant,
                    $contractWaiting,
                    $contractWaiting->getGroup()->isAllowedEditResidentId()
                );
            } catch (\Exception $e) {
                $this->getEntityManager()->rollback();
                $this->getLogger()->warning(
                    sprintf(
                        'Got error when trying move Contract from ContractWaiting#%d: %s',
                        $contractWaitingId,
                        $e->getMessage()
                    )
                );
                continue;
            }

            if (!$contract) {
                $this->getEntityManager()->rollback();
                $this->getLogger()->warning(
                    sprintf(
                        'Got validation errors when trying move Contract from ContractWaiting#%d: %s',
                        $contractWaitingId,
                        implode(', ', $contractManager->getErrors())
                    )
                );
                continue;
            }

            $contract->setStatus(ContractStatus::WAITING);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->commit();
            $this->getLogger()->info(
                sprintf(
                    'Migrated Contract#%d from ContractWaiting#%d',
                    $contract->getId(),
                    $contractWaitingId
                )
            );

            $this->getLogger()->debug('Clear entity manager on migrate waiting contract command');
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
        $contracts = $this->findDuplicateContractPerContractWaiting($contractWaiting);

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

    /**
     * @param ContractWaiting $contractWaiting
     * @return array
     */
    protected function findDuplicateContractPerContractWaiting(ContractWaiting $contractWaiting)
    {
        $query = $this
            ->getEntityManager()
            ->getRepository('RjDataBundle:Contract')
            ->createQueryBuilder('c')
            ->select('c.id')
            ->innerJoin('c.unit', 'u')
            ->innerJoin('c.tenant', 't')
            ->leftJoin('t.residentsMapping', 'rm')
            ->where('u.id = :unit')
            ->andWhere('c.status not in (:statuses)')
            ->setParameter('statuses', [ContractStatus::FINISHED, ContractStatus::DELETED])
            ->setParameter('unit', $contractWaiting->getUnit()->getId());

        if (!empty($contractWaiting->getExternalLeaseId())) {
            $query->andWhere('c.externalLeaseId = :leaseId')
                ->setParameter('leaseId', $contractWaiting->getExternalLeaseId());
        }

        if (!empty($contractWaiting->getResidentId())) {
            $query->andWhere('rm.residentId = :residentId')
                ->setParameter('residentId', $contractWaiting->getExternalLeaseId());
        }

        $result = $query->getQuery()->getScalarResult();

        if (empty($result)) {
            return [];
        }

        return array_map('current', $result);
    }
}
