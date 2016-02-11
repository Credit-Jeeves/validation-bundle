<?php

namespace RentJeeves\CoreBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\Collection;
use Monolog\Logger;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\DataBundle\Entity\LandlordRepository;
use RentJeeves\DataBundle\Entity\Landlord;
use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;

/**
 * @TODO: need refactoring - split for 2 function (for Landlord and Holding) and groom new function
 */
class EmailBatchDepositReportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('Email:batchDeposit:report')
            ->addOption(
                'date',
                null,
                InputOption::VALUE_OPTIONAL,
                'Deposit date in format YYYY-MM-DD'
            )
            ->addOption(
                'groupid',
                null,
                InputOption::VALUE_OPTIONAL,
                'Only send email for this Group ID'
            )
            ->addOption(
                'resend',
                null,
                InputOption::VALUE_OPTIONAL,
                'Set to true to add a "RESENT DUE TO DATA DELAY" header to email.'
            )
            ->setDescription('Send daily batch deposit report for landlords and holding admins');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = $input->getOption('date');
        if ($date) {
            $date = DateTime::createFromFormat('Y-m-d', $date);
        } else {
            $date = new DateTime();
        }

        $groupid = $input->getOption('groupid');
        $resend = $input->getOption('resend');

        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');

        $logger->info(sprintf('Preparing daily batch deposit report for %s', $date->format('m/d/Y')));
        if ($groupid) {
            $logger->info(sprintf('Only sending emails for group #%s', $groupid));
        }
        if ($resend) {
            $logger->info('Adding RESEND note to top of email.');
        }

        /** @var Mailer $mailer */
        $mailer = $this->getContainer()->get('project.mailer');
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var LandlordRepository $repoLandlord */
        $repoLandlord = $doctrine->getRepository('RjDataBundle:Landlord');
        /** @var TransactionRepository $repoTransaction */
        $repoTransaction = $doctrine->getRepository('RjDataBundle:Transaction');

        $logger->info('Sending emails to holding admins. ');
        $holdingAdmins = $repoLandlord->findNotPayDirectHoldingAdmins();
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
                $batchData = $repoTransaction->getBatchDepositedInfo($group, $date);
                $reversalData = $repoTransaction->getReversalDepositedInfo($group, $date);
                $groups[] = [
                    'groupName' => $group->getName(),
                    'accountNumber' => $group->getRentAccountNumberPerCurrentPaymentProcessor(),
                    'groupPaymentProcessor' => $group->getGroupSettings()->getPaymentProcessor(),
                    'batches' => $this->prepareBatchReportData($batchData),
                    'returns' => $this->prepareReversalTransactions($reversalData),
                ];
                if (!$needSend && (count($batchData) > 0 || count($reversalData) > 0)) {
                    if ($groupid) {  // if groupid option specified, only send for that group
                        $needSend = ($group->getId() == $groupid) ? true : false;
                    } else {         // otherwise send to everyone
                        $needSend = true;
                    }
                }
            }
            if ($needSend) {
                $logger->info(sprintf('Sending BatchDepositReportHolding to %s.', $holdingAdmin->getEmail()));
                if (!$mailer->sendBatchDepositReportHolding($holdingAdmin, $groups, $date, $resend)) {
                    $logger->info(sprintf('Sending email to %s failed. Check template', $holdingAdmin->getEmail()));
                } else {
                    $logger->info(sprintf('%s:BatchDepositReportHolding successfully sent', $holdingAdmin->getEmail()));
                }
            } else {
                $logger->info(sprintf(
                    '%s:BatchDepositReportHolding will not be sent -- needSend is false',
                    $holdingAdmin->getEmail()
                ));
            }
        }

        $logger->info('Sending emails to non-admins.');

        $landlords = $repoLandlord->findNotPayDirectHoldingNotAdmins();
        /** @var Landlord $landlord */
        foreach ($landlords as $landlord) {
            $agentGroups = $landlord->getAgentGroups();
            if (false === $landlord->getEmailNotification()) {
                $this->notifyOfEmailNotSent('BatchDepositReportLandlord', $landlord, $agentGroups);
                continue;
            }
            /** @var Group $group */
            foreach ($agentGroups as $group) {
                $batchData = $repoTransaction->getBatchDepositedInfo($group, $date);
                $reversalData = $repoTransaction->getReversalDepositedInfo($group, $date);
                // only send if no groupid option specified, or if groupid option matches current group
                if ((!$groupid) || ($groupid && ($group->getId() == $groupid))) {
                    $logger->info(sprintf(
                        'Sending BatchDepositReportLandlord to %s for group #%d "%s"',
                        $landlord->getEmail(),
                        $group->getId(),
                        $group->getName()
                    ));
                    if (count($batchData) > 0 || count($reversalData) > 0) {
                        if (!$mailer->sendBatchDepositReportLandlord(
                            $landlord,
                            $group,
                            $date,
                            $this->prepareBatchReportData($batchData),
                            $this->prepareReversalTransactions($reversalData),
                            $resend
                        )
                        ) {
                            $logger->info(sprintf('Sending email to %s failed. Check template', $landlord->getEmail()));
                        } else {
                            $logger->info(sprintf(
                                '%s:BatchDepositReportLandlord successfully sent for group %d',
                                $landlord->getEmail(),
                                $group->getId()
                            ));
                        }
                    } else {
                        $logger->info(sprintf(
                            'Will not sent email for group #%d "%s": deposits and reversals empty',
                            $group->getId(),
                            $group->getName()
                        ));
                    }
                }
            }
        }
    }

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
                $preparedData[] = [
                    'batchId' => $data[$i]['batchId'],
                    'paymentType' => $data[$i]['paymentType'],
                    'transactions' => $transactions,
                    'accountNumber' => $data[$i]['accountNumber'],
                    'depositAccountType' => DepositAccountType::title($data[$i]['depositAccountType']),
                    'paymentTotal' => number_format($paymentTotal, 2, '.', ''),
                ];
                $transactions = [];
                $paymentTotal = 0;
            }
        }

        return $preparedData;
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
        $this->getContainer()->get('logger')->warning(sprintf(
            '%s will not be sent to %s (groups %s): email notification choice is NO',
            $mailMethodName,
            $user->getEmail(),
            implode(',', $groupNames)
        ));
    }
}
