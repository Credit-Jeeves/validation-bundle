<?php

namespace RentJeeves\CoreBundle\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateResidentsToLeaseIdCommand extends BaseCommand
{
    const BATCH_SIZE = 30;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('renttrack:migrate:residents-to-lease-id')
            ->addOption(
                'accounting-system',
                null,
                InputOption::VALUE_REQUIRED,
                'AccountingSystem: amsi, mri bostonpost, yardi genesis v2 etc. Use double quotes: "accounting system".'
            )
            ->addOption(
                'jms-job-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'ID of job'
            )
            ->addOption(
                'leases-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Contract id seperated by comma. Example --lease-id="1,2,3,4,5"'
            )
            ->setDescription('Set up resident ID to lease ID for PROMAS. (or other lease-id based systems)');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $leasesId = $input->getOption('leases-id');
        $accountingSystem = $input->getOption('accounting-system');

        if (!AccountingSystem::isValid($accountingSystem)) {
            $this->getLogger()->info('SKIPPED|Accounting system name is wrong #' . $accountingSystem);

            return self::RESULT_FAILED;
        }

        if (empty($leasesId)) {
            return $this->createJobs($accountingSystem);
        } else {
            return $this->moveResidentIdToLeaseId(explode(',', $leasesId));
        }
    }

    /**
     * @param string $accountingName
     * @return int
     */
    protected function createJobs($accountingName)
    {
        $holdingQuery = 'SELECT h FROM CreditJeeves\DataBundle\Entity\Holding as h WHERE h.accountingSystem=\'%s\'';

        $holdingIterableResult = $this
            ->getEntityManager()
            ->createQuery(sprintf($holdingQuery, $accountingName))
            ->iterate(null, Query::HYDRATE_ARRAY);
        $contractsId = [];

        while ((list($holding) = $holdingIterableResult->next()) !== false) {

            $contractQuery = 'SELECT h FROM RentJeeves\DataBundle\Entity\Contract as h WHERE h.holding=\'%s\'';
            $contractQuery .= ' AND h.externalLeaseId IS NULL';

            $contractIterableResult = $this
                ->getEntityManager()
                ->createQuery(sprintf($contractQuery, $holding['id']))
                ->iterate(null, Query::HYDRATE_ARRAY);

            while ((list($contract) = $contractIterableResult->next()) !== false) {
                $contractsId[] = $contract['id'];

                if (count($contractsId) === self::BATCH_SIZE) {
                    $this->createJob($contractsId, $accountingName);
                    $contractsId = [];
                }
            }
        }

        if (!empty($contractsId)) {
            $this->createJob($contractsId, $accountingName);
        }

        return self::RESULT_SUCCESSFUL;
    }

    /**
     * @param array $contractsId
     * @param string $accountingName
     */
    protected function createJob(array $contractsId, $accountingName)
    {
        $command = 'renttrack:migrate:residents-to-lease-id';
        $parameterLeases = sprintf('--leases-id=%s', implode(',', $contractsId));
        $parameterAccountingSystem  = sprintf('--accounting-system=%s', $accountingName);
        $job = new Job($command, $parameters = [$parameterLeases, $parameterAccountingSystem]);
        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        $this->getLogger()->info(sprintf('Created command %s with parameter %s', $command, implode(' ', $parameters)));
    }

    /**
     * @param array $contractsId
     * @return int
     */
    protected function moveResidentIdToLeaseId(array $contractsId)
    {
        /** @var ContractRepository $contractRepository */
        $contractRepository = $this->getEntityManager()->getRepository('RjDataBundle:Contract');

        foreach ($contractsId as $id) {
            $this->getLogger()->debug('START TO MIGRATE|Contract #' . $id);
            /** @var Contract $contract */
            $contract = $contractRepository->find($id);

            if (empty($contract)) {
                $this->getLogger()->info('SKIPPED|Contract doesn\'t exist in DB #' . $id);
                continue;
            }

            if (!empty($contract->getExternalLeaseId())) {
                $this->getLogger()->info('SKIPPED|Contract already has exteranalLeaseId #' . $id);
                continue;
            }

            $residentMaping = $contract->getTenant()->getResidentForHolding($contract->getHolding());

            if (empty($residentMaping)) {
                $this->getLogger()->info('SKIPPED|Tenant doesn\'t have resident #' . $id);
                continue;
            }
            $this->getLogger()->info('MIGRATED|Contract #' . $id);
            $contract->setExternalLeaseId($residentMaping->getResidentId());
        }

        try {
            $this->getEntityManager()->flush();
        } catch (ORMException $e) {
            $this->getLogger()->warning($e->getMessage());
            return self::RESULT_FAILED;
        }

        return self::RESULT_SUCCESSFUL;
    }
}
