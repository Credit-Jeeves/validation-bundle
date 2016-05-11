<?php

namespace RentJeeves\CoreBundle\Command;

use Monolog\Logger;
use RentJeeves\LandlordBundle\BatchDeposits\EmailBatchDepositReportManager;
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
    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupId = $input->getOption('groupid');
        $resend = $input->getOption('resend');
        $date = $this->getDate($input->getOption('date'));

        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');

        $logger->info(sprintf('Preparing daily batch deposit report for %s', $date->format('m/d/Y')));
        if ($groupId) {
            $logger->info(sprintf('Only sending emails for group #%s', $groupId));
        }
        if ($resend) {
            $logger->info('Adding RESEND note to top of email.');
        }

        $emailBatchDepositManager = $this->getEmailBatchDepositReportManager();

        $emailBatchDepositManager->sendEmailReportToHoldingAdmins($date, $groupId, $resend);

        $emailBatchDepositManager->sendEmailReportToHoldingNonAdmins($date, $groupId, $resend);
    }

    /**
     * @param string $dateOption
     * @return DateTime
     */
    protected function getDate($dateOption)
    {
        if ($dateOption) {
            return DateTime::createFromFormat('Y-m-d', $dateOption);
        }

        return new DateTime();
    }

    /**
     * @return EmailBatchDepositReportManager
     */
    protected function getEmailBatchDepositReportManager()
    {
        return $this->getContainer()->get('landlord.batch_deposits.email_manager');
    }
}
