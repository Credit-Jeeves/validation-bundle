<?php

namespace RentJeeves\CoreBundle\Command;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Registry;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\HeartlandRepository;
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
            ->setDescription('Send daily batch deposit report for landlords and holding admins');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new DateTime();

        $output->writeln('Start prepare daily batch deposit report by ' . $date->format('m/d/Y'));

        /** @var Mailer $mailer */
        $mailer = $this->getContainer()->get('project.mailer');
        /** @var Registry $doctrine */
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var LandlordRepository $repoLandlord */
        $repoLandlord = $doctrine->getRepository('RjDataBundle:Landlord');
        /** @var HeartlandRepository $repoHeartland */
        $repoHeartland = $doctrine->getRepository('RjDataBundle:Heartland');

        $holdingAdmins = $repoLandlord->findBy(['is_super_admin' => true], ['email' => 'DESC']);
        foreach ($holdingAdmins as $holdingAdmin) {
            $needSend = false;
            /** @var Landlord $holdingAdmin */
            $groups = [];
            foreach ($holdingAdmin->getGroups() as $group) {
                /** @var Group $group */
                $batchData = $repoHeartland->getBatchDepositedInfo($group, $date);
                $reversalData = $repoHeartland->getReversalDepositedInfo($group, $date);
                $groups[] = [
                    'groupName' => $group->getName(),
                    'accountNumber' => $group->getAccountNumber(),
                    'batches' => $this->prepareBatchReportData($batchData),
                    'returns' => $reversalData,
                ];
                if (!$needSend && (count($batchData) > 0 || count($reversalData) > 0)) {
                    $needSend = true;
                }
            }
            !$needSend || $mailer->sendBatchDepositReportHolding($holdingAdmin, $groups, $date);
        }

        $landlords = $repoLandlord->findBy(['is_super_admin' => false], ['email' => 'DESC']);
        foreach ($landlords as $landlord) {
            /** @var Landlord $landlord */
            foreach ($landlord->getAgentGroups() as $group) {
                /** @var Group $group */
                $batchData = $repoHeartland->getBatchDepositedInfo($group, $date);
                $reversalData = $repoHeartland->getReversalDepositedInfo($group, $date);
                if (count($batchData) > 0 || count($reversalData) > 0) {
                    $mailer->sendBatchDepositReportLandlord(
                        $landlord,
                        $group,
                        $date,
                        $this->prepareBatchReportData($batchData),
                        $reversalData
                    );
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
