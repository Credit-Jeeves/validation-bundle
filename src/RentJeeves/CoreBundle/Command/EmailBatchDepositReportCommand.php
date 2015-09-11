<?php

namespace RentJeeves\CoreBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\DataBundle\Entity\LandlordRepository;
use RentJeeves\DataBundle\Entity\Landlord;
use CreditJeeves\DataBundle\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;

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

        $output->writeln('Preparing daily batch deposit report for ' . $date->format('m/d/Y'));
        if ($groupid) {
            $output->writeln('Only sending emails for group id ' . $groupid);
        }
        if ($resend) {
            $output->writeln('Adding RESEND note to top of email.');
        }

        /** @var Mailer $mailer */
        $mailer = $this->getContainer()->get('project.mailer');
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var LandlordRepository $repoLandlord */
        $repoLandlord = $doctrine->getRepository('RjDataBundle:Landlord');
        /** @var TransactionRepository $repoTransaction */
        $repoTransaction = $doctrine->getRepository('RjDataBundle:Transaction');

        $output->writeln('Sending emails to holding admins.');

        $holdingAdmins = $repoLandlord->findBy(['is_super_admin' => true], ['email' => 'DESC']);
        foreach ($holdingAdmins as $holdingAdmin) {
            $needSend = false;
            /** @var Landlord $holdingAdmin */
            $groups = [];
            foreach ($holdingAdmin->getGroups() as $group) {
                /** @var Group $group */
                $batchData = $repoTransaction->getBatchDepositedInfo($group, $date);
                $reversalData = $repoTransaction->getReversalDepositedInfo($group, $date);
                $groups[] = [
                    'groupName' => $group->getName(),
                    'accountNumber' => $group->getRentAccountNumberPerCurrentPaymentProcessor(),
                    'groupPaymentProcessor' => $group->getGroupSettings()->getPaymentProcessor(),
                    'batches' => $this->prepareBatchReportData($batchData),
                    'returns' => $reversalData,
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
                $mailer->sendBatchDepositReportHolding($holdingAdmin, $groups, $date, $resend);
                $output->write('.');
            }
        }

        $output->writeln('');
        $output->writeln('Sending emails to non-admins.');

        $landlords = $repoLandlord->findBy(['is_super_admin' => false], ['email' => 'DESC']);
        foreach ($landlords as $landlord) {
            /** @var Landlord $landlord */
            foreach ($landlord->getAgentGroups() as $group) {
                /** @var Group $group */
                $batchData = $repoTransaction->getBatchDepositedInfo($group, $date);
                $reversalData = $repoTransaction->getReversalDepositedInfo($group, $date);
                // only send if no groupid option specified, or if groupid option matches current group
                if ((!$groupid) || ($groupid && ($group->getId() == $groupid))) {
                    if (count($batchData) > 0 || count($reversalData) > 0) {
                        $mailer->sendBatchDepositReportLandlord(
                            $landlord,
                            $group,
                            $date,
                            $this->prepareBatchReportData($batchData),
                            $reversalData,
                            $resend
                        );
                        $output->write('.');
                    }
                }
            }
        }
        $output->writeln('');
        $output->writeln('Sending batch deposit report for ' . $date->format('m/d/Y') . ' complete.');
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
            if (($i+1) == $count || $data[$i]['batchId'] != $data[$i+1]['batchId']) {
                $preparedData[] = [
                    'batchId' => $data[$i]['batchId'],
                    'paymentType' => $data[$i]['paymentType'],
                    'transactions' => $transactions,
                    'paymentTotal' => number_format($paymentTotal, 2, '.', ''),
                ];
                $transactions = [];
                $paymentTotal = 0;
            }
        }

        return $preparedData;
    }
}
