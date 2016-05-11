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
                'AccountingSystem: amsi, mri bostonpost, yardi genesis v2 etc'
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
                'Contract id seperated by comma. Example 1,2,3,4,5'
            )
            ->setDescription('Set up resident ID to lease ID for PROMAS');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $leasesId = $input->getOption('leases-id');
        $accountingSystem = $input->getOption('accounting-system');

        if (!AccountingSystem::isValid($accountingSystem)) {
            $this->getLogger()->debug('SKIPPED|Accounting system name is wrong #' . $accountingSystem);

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
        $query = 'SELECT h FROM CreditJeeves\DataBundle\Entity\Holding as h WHERE h.accountingSystem=\'%s\'';

        $iterableResult = $this
            ->getEntityManager()
            ->createQuery(sprintf($query, $accountingName))
            ->iterate(null, Query::HYDRATE_ARRAY);
        $contractsId = [];
        $contractRepository = $this->getEntityManager()->getRepository('RjDataBundle:Contract');

        /** @var Holding $holding */
        while ((list($holding) = $iterableResult->next()) !== false) {
            $contracts = $contractRepository->getContractsIdByHoldingAndEmptyLeaseId($holding['id']);

            foreach ($contracts as $contract) {
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
        $parameterLeases = sprintf('--leases-id="%s"', implode(',', $contractsId));
        $parameterAccountingSystem  = sprintf('--accounting-system="%s"', $accountingName);
        $job = new Job($command, $parameters = [$parameterLeases, $parameterAccountingSystem]);
        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        $this->getLogger()->info(sprintf('Created command %s parameter %s', $command, implode(' ', $parameters)));
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
            /** @var Contract $contract */
            $contract = $contractRepository->find($id);

            if (empty($contract)) {
                $this->getLogger()->debug('SKIPPED|Contract doesn\'t exist in DB #' . $id);
                continue;
            }

            if (!empty($contract->getExternalLeaseId())) {
                $this->getLogger()->debug('SKIPPED|Contract already has exteranalLeaseId #' . $id);
                continue;
            }

            $residentMaping = $contract->getTenant()->getResidentForHolding($contract->getHolding());

            if (empty($residentMaping)) {
                $this->getLogger()->debug('SKIPPED|Tenant doesn\'t have resident #' . $id);
                continue;
            }
            $this->getLogger()->debug('MIGRATE|Contract #' . $id);
            $contract->setExternalLeaseId($residentMaping->getResidentId());
        }

        try {
            $this->getEntityManager()->flush();
        } catch(ORMException $e) {
            $this->getLogger()->warning($e->getMessage());
            return self::RESULT_FAILED;
        }

        return self::RESULT_SUCCESSFUL;
    }
}
