<?php

namespace RentJeeves\CoreBundle\Services\Emails;

use CreditJeeves\DataBundle\Entity\Operation;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\CoreBundle\Helpers\PeriodicExecutor;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\SoftDeleteableControl;
use RentJeeves\DataBundle\Entity\Contract;

/**
 * This class should send email for tenant
 */
class RentIsDueEmailSender
{
    const LIMIT_PER_PAGE = 100;

    const EM_CLEANUP_PERIOD = 500;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SoftDeleteableControl
     */
    protected $softDeleteableControl;

    /**
     * @var DateTime
     */
    protected $shiftedDate;

    /**
     * @var PeriodicExecutor
     */
    protected $periodicExecutor;

    /**
     * @var bool
     */
    protected $dryRunMode = false;

    /**
     * @var int
     */
    protected $startSendFromContractId;

    /**
     * @param EntityManager $em
     * @param Mailer $mailer
     * @param LoggerInterface $logger
     * @param SoftDeleteableControl $softDeleteableControl
     */
    public function __construct(
        EntityManager $em,
        Mailer $mailer,
        LoggerInterface $logger,
        SoftDeleteableControl $softDeleteableControl
    ) {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->softDeleteableControl = $softDeleteableControl;
        $this->shiftedDate = new DateTime('now');
        // setup running EM cleanup periodically
        $this->periodicExecutor = new PeriodicExecutor(
            $this,
            'cleanupDoctrineCallback',
            self::EM_CLEANUP_PERIOD,
            $logger
        );
    }

    /**
     * @return boolean
     */
    public function isDryRunMode()
    {
        return $this->dryRunMode;
    }

    /**
     * @param boolean $dryRun
     */
    public function setDryRunMode($dryRun)
    {
        $this->dryRunMode = (boolean) $dryRun;
    }

    /**
     * @return int
     */
    public function getStartSendFromContractId()
    {
        return $this->startSendFromContractId;
    }

    /**
     * @param int $startSendFromContractId
     */
    public function setStartSendFromContractId($startSendFromContractId)
    {
        $this->startSendFromContractId = $startSendFromContractId;
    }

    /**
     * Since this can be a long running batch script, we need to clean up some stuff in the EM periodically
     * to avoid having doctrine slow WAY down.
     */
    public function cleanupDoctrineCallback()
    {
        $this->logger->info('Clearing entity manager');
        $this->em->clear();
    }

    /**
     * @param string $modify
     */
    public function modifyShiftedDate($modify)
    {
        $this->logger->info(sprintf('Modify shifted date %s', $modify));
        $this->shiftedDate->modify($modify);
    }

    /**
     * This function will send payment due emails to tenants
     */
    public function findContractsAndSendPaymentDueEmails()
    {
        $this->logger->info(sprintf('Starting work %s', $this->isDryRunMode()? 'in DRY RUN MODE' : ''));

        $this->softDeleteableControl->disable();
        $totalContracts = $this->em->getRepository('RjDataBundle:Contract')->countContractsForSendTenantEmail(
            $this->shiftedDate
        );

        $this->logger->info(sprintf('Total contracts %s', $totalContracts));

        if ($totalContracts === 0) {
            $this->logger->info('Finished work');
            return;
        }

        $pages = ceil($totalContracts / self::LIMIT_PER_PAGE);
        $this->logger->info(sprintf('Pages %s', $pages));

        for ($i = 1; $i <= $pages; $i++) {
            $offset = ($i - 1) * self::LIMIT_PER_PAGE;
            $contractsId = $this->em->getRepository('RjDataBundle:Contract')->getContractsIdForSendTenantEmail(
                $this->shiftedDate,
                $offset,
                self::LIMIT_PER_PAGE
            );
            $this->logger->info(sprintf('Send emails for contract: %s', implode(',', $contractsId)));
            $this->sendPaymentDueEmailsByContractIds($contractsId);
        }
        $this->logger->info('Finished work');
    }

    /**
     * This method is public because we can use it for jobs in future
     *
     * @param array $contractsId
     * @return void
     */
    public function sendPaymentDueEmailsByContractIds(array $contractsId)
    {
        foreach ($contractsId as $contractId) {
            try {
                $this->logger->info(sprintf('Send emails for contract: %s', $contractId));
                /** @var Contract $contract */
                $contract = $this->em->getRepository('RjDataBundle:Contract')->find($contractId);
                if (empty($contract)) {
                    throw new \LogicException(sprintf('Contract not found %s', $contractId));
                }

                if ($contract->getTenant()->getEmailNotification() === false) {
                    throw new \LogicException(
                        sprintf('User %s disabled his notification in the settings', $contract->getTenant()->getEmail())
                    );
                }

                $result = $this->sendPaymentDueEmailByContract($contract);
                if ($result === false) {
                    throw new \RuntimeException(sprintf('We could not send the email for contractId %s', $contractId));
                }
                $this->periodicExecutor->increment();
            } catch (\Exception $e) {
                $this->logger->info(
                    sprintf(
                        'Got exception (%s) when process contractId %s',
                        $e->getMessage(),
                        $contractId
                    )
                );
            }
        }
    }

    /**
     * @param Contract $contract
     * @return boolean
     */
    public function sendPaymentDueEmailByContract(Contract $contract)
    {
        $email = $contract->getTenant()->getEmail();
        if ($this->getStartSendFromContractId() && $contract->getId() < $this->getStartSendFromContractId()) {
            $this->logger->info(
                sprintf(
                    'This contract ID %s %s skipped, because we should start send from ID %s',
                    $contract->getId(),
                    $email,
                    $this->getStartSendFromContractId()
                )
            );

            return true;
        }

        $isPayLastThreeMonth = $this->isPayLastThreeMonth($contract);

        if (!$isPayLastThreeMonth) {
            $this->logger->debug(sprintf('This contract %s %s not pay last three month', $contract->getId(), $email));

            return true;
        }

        $activeRentPayment = $contract->getActiveRentPayment();

        if (empty($activeRentPayment)) {
            return $this->sendRjDueEmailWhichNotHaveActivePayment($contract);
        }

        if ($this->isNextDueDateMoreThanFinishAt($contract)) {
            $this->logger->debug(
                sprintf(
                    'Contract finishAt more than dueDate. Not send, contract ID %s %s',
                    $contract->getId(),
                    $email
                )
            );

            return true;
        }

        $this->logger->info(
            sprintf(
                'Will send email %s, contract %s %s',
                $activeRentPayment->getType(),
                $contract->getId(),
                $email
            )
        );

        if ($this->isDryRunMode()) {
            return true;
        }

        return $this->mailer->sendRjPaymentDue(
            $contract,
            $activeRentPayment->getType(),
            $this->isEndedPayment($contract),
            $activeRentPayment->getTotal()
        );
    }

    /**
     * @param Contract $contract
     * @return bool
     */
    protected function sendRjDueEmailWhichNotHaveActivePayment(Contract $contract)
    {
        $this->logger->debug(
            sprintf(
                'This contract %s not have active rent payment, so will send email',
                $contract->getId()
            )
        );

        if ($this->isDryRunMode()) {
            return true;
        }

        return $this->mailer->sendRjPaymentDue($contract);
    }

    /**
     * @param Contract $contract
     * @return bool
     */
    protected function isPayLastThreeMonth(Contract $contract)
    {
        /** @var Operation $operation */
        $operation = $this->em->getRepository('DataBundle:Operation')->findOneBy(
            ['contract' => $contract],
            ['createdAt' => 'DESC']
        );

        if (empty($operation)) {
            return false;
        }

        $threeMonthAgo = new \DateTime('-3 month');

        if ($operation->getCreatedAt() > $threeMonthAgo) {
            return true;
        }

        return false;
    }

    /**
     * @param Contract $contract
     * @return bool
     */
    protected function isEndedPayment(Contract $contract)
    {
        $activePayment = $contract->getActiveRentPayment();
        $nextPaymentDate = $activePayment->getNextPaymentDate();
        $nextPaymentDate->setTime(0, 0, 0);

        $month = $activePayment->getEndMonth();
        $year = $activePayment->getEndYear();
        $dueDate = $activePayment->getDueDate();

        $endDate = new DateTime();
        $endDate->setDate($year, $month, $dueDate);
        $endDate->setTime(0, 0, 0);

        return $nextPaymentDate > $endDate;
    }

    /**
     * @param Contract $contract
     * @return boolean
     */
    protected function isNextDueDateMoreThanFinishAt(Contract $contract)
    {
        $activePayment = $contract->getActiveRentPayment();
        $nextPaymentDate = $activePayment->getNextPaymentDate();
        $nextPaymentDate->setTime(0, 0, 0);

        $finishAt = $contract->getFinishAt();

        if (empty($finishAt)) {
            return false;
        }

        $finishAt->setTime(0, 0, 0);

        return $nextPaymentDate > $finishAt;
    }
}
