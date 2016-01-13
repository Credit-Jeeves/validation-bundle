<?php
namespace RentJeeves\CoreBundle\Command;

use RentJeeves\CoreBundle\Services\Emails\RentIsDueEmailSender;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EmailTenantCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('email:tenant')
            ->setDescription('Send Payment Due Emails')
            ->addOption(
                'days',
                null,
                InputOption::VALUE_OPTIONAL,
                'Days before due date',
                '5'
            )
            ->addOption(
                'dry-run-mode',
                null,
                InputOption::VALUE_OPTIONAL,
                'Use for just test and see for what contract->user we send emails',
                false
            )
            ->addOption(
                'start-from-contract-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Start send emails from contract ID'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var RentIsDueEmailSender $rentIsDueEmailSender */
        $rentIsDueEmailSender = $this->getContainer()->get('rent.is_due.email_sender');
        $rentIsDueEmailSender->modifyShiftedDate(sprintf('+%s days', $input->getOption('days')));
        $rentIsDueEmailSender->setDryRunMode($input->getOption('dry-run-mode'));
        $rentIsDueEmailSender->setStartSendFromContractId($input->getOption('start-from-contract-id'));
        $rentIsDueEmailSender->findContractsAndSendPaymentDueEmails();
    }
}
