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

class EmailBatchDepositReportCommand  extends ContainerAwareCommand
{
    /**
     * @const sting
     */
    const OPTION_DATE = 'date';

    protected function configure()
    {
        $description = 'Send daily batch deposit report by email for landlords and holding admins';
        $this
            ->setName('Email:batchDeposit:report')
            ->setDescription($description)
            ->addOption(
                self::OPTION_DATE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Select date for report, default today'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dateString = $input->getOption(self::OPTION_DATE);
        if ($dateString) {
            $date = new DateTime($dateString);
        } else {
            $date = new DateTime();
        }
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
            /** @var Landlord $holdingAdmin */
            $groups = [];
            foreach ($holdingAdmin->getGroups() as $group) {
                /** @var Group $group */
                $data = $repoHeartland->getBatchDepositedInfo($group, $date->format('Y-m-d'));
                $groups[] = [
                    'groupName' => $group->getName(),
                    'accountNumber' => $group->getDepositAccount()->getAccountNumber(),
                    'batches' => $this->prepareData($data)
                ];
            }
            $mailer->sendBatchDepositReport($holdingAdmin, $groups, $date);
        }

        $landlords = $repoLandlord->findBy(['is_super_admin' => false], ['email' => 'DESC']);
        foreach($landlords as $landlord) {
            /** @var Landlord $landlord */
            foreach ($landlord->getAgentGroups() as $group) {
                /** @var Group $group */
                $data = $repoHeartland->getBatchDepositedInfo($group, $date->format('Y-m-d'));
                $mailer->sendBatchDepositReport($landlord, $group, $date, $this->prepareData($data));
            }
        }
    }

    protected function prepareData($data)
    {
        $translator = $this->getContainer()->get('translator.default');
        $preparedData = [];
        $count = count($data);
        $transactions = [];
        $paymentTotal = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($data[$i]['status'] == OrderStatus::COMPLETE) {
                $paymentTotal += $data[$i]['amount'];
            }
            $data[$i]['status'] = $translator->trans('order.status.text.'.$data[$i]['status']);
            $paymentType = $translator->trans('order.type.'.$data[$i]['paymentType']);
            $transactions[] = $data[$i];
            $tmpArr = [
                'batchId' => $data[$i]['batchId'],
                'paymentType' => $paymentType,
                'transactions' => $transactions,
                'paymentTotal' => $paymentTotal
            ];

            if (($i+1) == $count || $data[$i]['batchId'] != $data[$i+1]['batchId']) {
                $transactions = [];
                $preparedData[] = $tmpArr;
                $paymentTotal = 0;
            }
        }
        return $preparedData;
    }
}
