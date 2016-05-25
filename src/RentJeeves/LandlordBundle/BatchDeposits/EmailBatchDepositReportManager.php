<?php
namespace RentJeeves\LandlordBundle\BatchDeposits;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\LandlordRepository;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\LandlordBundle\Accounting\Export\Report\ExportReport;
use RentJeeves\LandlordBundle\BatchDeposits\ExportReport\ExportReportFactory;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class EmailBatchDepositReportManager
{
    /** @var EntityManagerInterface $em */
    protected $em;

    /** @var Mailer $mailer */
    protected $mailer;

    /** @var LoggerInterface $logger */
    protected $logger;

    /** @var ExportReportFactory $export */
    protected $exportReportFactory;

    /**
     * EmailBatchDepositReportManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param Mailer $mailer
     * @param LoggerInterface $logger
     * @param ExportReportFactory $exportReportFactory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Mailer $mailer,
        LoggerInterface $logger,
        ExportReportFactory $exportReportFactory
    ) {
        $this->em = $entityManager;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->exportReportFactory = $exportReportFactory;
    }

    /**
     * @param \DateTime $date
     * @param array $groupIds
     * @param string $resend
     */
    public function sendEmailReportToHoldingAdmins(\DateTime $date, $groupIds = null, $resend = null)
    {
        $this->logger->info('Sending emails to holding admins.');

        $holdingAdmins = $this->getLandlordRepository()->findNotPayDirectHoldingAdmins();
        /** @var Landlord $holdingAdmin */
        foreach ($holdingAdmins as $holdingAdmin) {
            $adminGroups = $holdingAdmin->getGroups();
            if (false === $holdingAdmin->getEmailNotification()) {
                $this->notifyOfEmailNotSent('BatchDepositReportHolding', $holdingAdmin, $adminGroups);
                continue;
            }
            $needSend = false;
            $groups = [];
            /** @var Group $group */
            foreach ($adminGroups as $group) {
                $batchData = $this->getTransactionRepository()->getBatchDepositedInfo($group, $date);
                $reversalData = $this->getTransactionRepository()->getReversalDepositedInfo($group, $date);

                if (!$groupIds || ($groupIds && in_array($group->getId(), $groupIds))) {
                    $groups[] = $this->getPreparedParamsBeforeSendForGroup($group, $batchData, $reversalData);
                }

                if (!$needSend && (count($batchData) > 0 || count($reversalData) > 0)) {
                    $needSend = ($groupIds) ? in_array($group->getId(), $groupIds) : true;
                }
            }

            $this->notifyHoldingAdmin($holdingAdmin, $groups, $date, $needSend, $resend);
        }
    }

    /**
     * @param \DateTime $date
     * @param array $groupIds
     * @param string $resend
     */
    public function sendEmailReportToHoldingNonAdmins(\DateTime $date, $groupIds = null, $resend = null)
    {
        $this->logger->info('Sending emails to non-admins.');

        $landlords = $this->getLandlordRepository()->findNotPayDirectHoldingNotAdmins();
        /** @var Landlord $landlord */
        foreach ($landlords as $landlord) {
            $agentGroups = $landlord->getAgentGroups();
            if (false === $landlord->getEmailNotification()) {
                $this->notifyOfEmailNotSent('BatchDepositReportLandlord', $landlord, $agentGroups);
                continue;
            }
            /** @var Group $group */
            foreach ($agentGroups as $group) {
                if (!$groupIds || ($groupIds && in_array($group->getId(), $groupIds))) {
                    $this->notifyLandlordIsNotAdmin($landlord, $group, $date, $resend);
                }
            }
        }
    }

    /**
     * @param Landlord $holdingAdmin
     * @param array $groups
     * @param \DateTime $date
     * @param $needSend
     * @param $resend
     */
    protected function notifyHoldingAdmin(
        Landlord $holdingAdmin,
        array $groups,
        \DateTime $date,
        $needSend,
        $resend
    ) {
        if ($needSend) {
            $this->logger->info(sprintf('Sending BatchDepositReportHolding to %s.', $holdingAdmin->getEmail()));

            // get CSV for report (if supported)
            $pathToCsvReport = null;
            $exportReport = $this->getExportReport($holdingAdmin->getHolding());
            if ($exportReport) {
                $pathToCsvReport = $this->getPathToCsvReport(
                    $exportReport,
                    $holdingAdmin,
                    $date,
                    $group = null,
                    $groups
                );
            }

            $result = $this->mailer->sendBatchDepositReportHolding(
                $holdingAdmin,
                $groups,
                $date,
                $resend,
                $pathToCsvReport
            );

            if ($pathToCsvReport) {
                unlink($pathToCsvReport);
            }

            if (false === $result) {
                $this->logger->info(
                    sprintf('Sending email to %s failed. Check template', $holdingAdmin->getEmail())
                );
            } else {
                $this->logger->info(
                    sprintf('%s:BatchDepositReportHolding successfully sent', $holdingAdmin->getEmail())
                );
            }
        } else {
            $this->logger->info(
                sprintf(
                    '%s:BatchDepositReportHolding will not be sent -- needSend is false',
                    $holdingAdmin->getEmail()
                )
            );
        }
    }

    /**
     * @param Landlord $landlord
     * @param Group $group
     * @param \DateTime $date
     * @param string $resend
     */
    protected function notifyLandlordIsNotAdmin(Landlord $landlord, Group $group, \DateTime $date, $resend)
    {
        $batchData = $this->getTransactionRepository()->getBatchDepositedInfo($group, $date);
        $reversalData = $this->getTransactionRepository()->getReversalDepositedInfo($group, $date);

        $this->logger->info(
            sprintf(
                'Sending BatchDepositReportLandlord to %s for group #%d "%s"',
                $landlord->getEmail(),
                $group->getId(),
                $group->getName()
            )
        );
        if (count($batchData) > 0 || count($reversalData) > 0) {
            // get CSV for report (if supported)
            $pathToCsvReport = null;
            $exportReport = $this->getExportReport($group->getHolding());
            if ($exportReport) {
                $pathToCsvReport = $this->getPathToCsvReport($exportReport, $landlord, $date, $group);
            }

            $result = $this->mailer->sendBatchDepositReportLandlord(
                $landlord,
                $group,
                $date,
                $this->prepareBatchReportData($batchData),
                $this->prepareReversalTransactions($reversalData),
                $resend,
                $pathToCsvReport
            );

            if ($pathToCsvReport) {
                unlink($pathToCsvReport);
            }

            if (false === $result) {
                $this->logger->info(sprintf('Sending email to %s failed. Check template', $landlord->getEmail()));
            } else {
                $this->logger->info(
                    sprintf(
                        '%s:BatchDepositReportLandlord successfully sent for group %d',
                        $landlord->getEmail(),
                        $group->getId()
                    )
                );
            }
        } else {
            $this->logger->info(
                sprintf(
                    'Will not sent email for group #%d "%s": deposits and reversals empty',
                    $group->getId(),
                    $group->getName()
                )
            );
        }
    }

    /**
     * @param Group $group
     * @param $batchData
     * @param $reversalData
     * @return array
     */
    protected function getPreparedParamsBeforeSendForGroup(Group $group, $batchData, $reversalData)
    {
        return [
            'id' => $group->getId(),
            'groupName' => $group->getName(),
            'accountNumber' => $group->getRentAccountNumberPerCurrentPaymentProcessor(),
            'groupPaymentProcessor' => $group->getGroupSettings()->getPaymentProcessor(),
            'batches' => $this->prepareBatchReportData($batchData),
            'returns' => $this->prepareReversalTransactions($reversalData),
        ];
    }

    /**
     * @param $data
     * @return array
     */
    protected function prepareBatchReportData($data)
    {
        $preparedData = [];
        $count = count($data);
        $transactions = [];
        $paymentTotal = 0;
        for ($i = 0; $i < $count; $i++) {
            $paymentTotal += $data[$i]['amount'];
            $transactions[] = $data[$i];

            /** Need check that next.batch_id != current.batch_id, also check that next exists */
            if (($i + 1) == $count || $data[$i]['batchId'] != $data[$i + 1]['batchId']) {
                $depositType = $data[$i]['depositAccountType'];
                $friendlyName = $data[$i]['friendlyName'];
                $depositType = !empty($friendlyName) ? $friendlyName : DepositAccountType::title($depositType);

                $preparedData[] = [
                    'batchId' => $data[$i]['batchId'],
                    'paymentType' => $data[$i]['paymentType'],
                    'transactions' => $transactions,
                    'accountNumber' => $data[$i]['accountNumber'],
                    'depositAccountType' => $depositType,
                    'paymentTotal' => number_format($paymentTotal, 2, '.', ''),
                ];
                $transactions = [];
                $paymentTotal = 0;
            }
        }

        return $preparedData;
    }

    /**
     * @param ExportReport $exportReport
     * @param Landlord $landlord
     * @param \DateTime $date
     * @param Group|null $group
     * @param array $groups
     * @return string
     * @throws \RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException
     */
    protected function getPathToCsvReport(
        ExportReport $exportReport,
        Landlord $landlord,
        \DateTime $date,
        Group $group = null,
        array $groups = null
    ) {
        $content = $this->getExportContent($exportReport, $landlord, $date, $group, $groups);
        $tmpFilePath = sprintf(
            '%s%s%s_%s',
            sys_get_temp_dir(),
            DIRECTORY_SEPARATOR,
            uniqid(),
            $exportReport->getFilename()
        );

        $handle = fopen($tmpFilePath, "w");
        fwrite($handle, $content);
        fclose($handle);

        return $tmpFilePath;
    }

    /**
     * @param ExportReport $exportReport
     * @param Landlord $landlord
     * @param \DateTime $date
     * @param Group|null $group
     * @param array|null $groups
     * @return string
     */
    protected function getExportContent(
        ExportReport $exportReport,
        Landlord $landlord,
        \DateTime $date,
        Group $group = null,
        array $groups = null
    ) {
        if (null !== $groups) {
            $groups = array_column($groups, 'id');
        }
        return $exportReport->getContent(
            [
                'landlord' => $landlord,
                'group' => $group,
                'groupIds' => $groups,
                'export_by' => ExportReport::EXPORT_BY_DEPOSITS,
                'begin' => $date->format('Y-m-d'),
                'end' => $date->format('Y-m-d'),
            ]
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareReversalTransactions(array $data)
    {
        $batches = [];
        foreach ($data as $reversalTransaction) {
            $batches[$reversalTransaction['batchId']]['transactions'][] = $reversalTransaction;
            if (true === isset($batches[$reversalTransaction['batchId']]['paymentTotal'])) {
                $batches[$reversalTransaction['batchId']]['paymentTotal'] += $reversalTransaction['amount'];
            } else {
                $batches[$reversalTransaction['batchId']]['paymentTotal'] = $reversalTransaction['amount'];
            }
        }

        return $batches;
    }

    /**
     * @return LandlordRepository
     */
    protected function getLandlordRepository()
    {
        return $this->em->getRepository('RjDataBundle:Landlord');
    }

    /**
     * @return TransactionRepository
     */
    protected function getTransactionRepository()
    {
        return $this->em->getRepository('RjDataBundle:Transaction');
    }

    /**
     * @param string $mailMethodName
     * @param Landlord $user
     * @param Collection $groups
     */
    protected function notifyOfEmailNotSent($mailMethodName, Landlord $user, Collection $groups)
    {
        $groupNames = [];
        foreach ($groups as $group) {
            $groupNames[] = $group->getName();
        }
        $this->logger->warning(
            sprintf(
                '%s will not be sent to %s (groups %s): email notification choice is NO',
                $mailMethodName,
                $user->getEmail(),
                implode(',', $groupNames)
            )
        );
    }

    /**
     * @param Holding $holding
     * @return null|ExportReport
     */
    protected function getExportReport(Holding $holding = null)
    {
        return $this->exportReportFactory->getExportReport($holding);
    }
}
