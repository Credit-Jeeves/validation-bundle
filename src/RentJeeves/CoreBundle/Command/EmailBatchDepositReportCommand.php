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
                'group-ids',
                null,
                InputOption::VALUE_OPTIONAL,
                'Send email for list Group IDs. Example: --group-ids=1,2,3...n'
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
        $groupIds = $input->getOption('group-ids');
        $resend = $input->getOption('resend');
        $date = $this->getDate($input->getOption('date'));

        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');

        $logger->info(sprintf('Preparing daily batch deposit report for %s', $date->format('m/d/Y')));
        if ($groupIds) {
            $logger->info(sprintf('Only sending emails for groups #(%s)', $groupIds));
            $groupIds = explode(',', $groupIds);
        }
        if ($resend) {
            $logger->info('Adding RESEND note to top of email.');
        }

        $emailBatchDepositManager = $this->getEmailBatchDepositReportManager();

        $emailBatchDepositManager->sendEmailReportToHoldingAdmins($date, $groupIds, $resend);

        $emailBatchDepositManager->sendEmailReportToHoldingNonAdmins($date, $groupIds, $resend);
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
