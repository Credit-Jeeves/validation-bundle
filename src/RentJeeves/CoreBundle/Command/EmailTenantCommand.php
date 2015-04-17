<?php
namespace RentJeeves\CoreBundle\Command;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\DataBundle\Enum\PaymentType;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use CreditJeeves\DataBundle\Enum\UserType;
use RentJeeves\CoreBundle\Traits\DateCommon;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\CoreBundle\DateTime;

class EmailTenantCommand extends ContainerAwareCommand
{
    use DateCommon;

    /**
     * @var string
     */
    const OPTION_TYPE = 'type';

    /**
     * @var string
     */
    const OPTION_DAYS = 'days';

    /**
     * @var string
     */
    const OPTION_AUTO = 'auto';

    /**
     * @var string
     */
    const OPTION_DRY_RUN = 'dry-run';

    /**
     * @var string
     */
    const OPTION_START_AT_EMAIL = 'start-at';

    /**
     * @var string
     */
    const OPTION_TYPE_DEFAULT = 'due';

    /**
     * @var string
     */
    const OPTION_TYPE_LATE = 'late';

    /**
     * @var string
     */
    const OPTION_TYPE_INVITED = 'invited';

    /**
     * @var string
     */
    const OPTION_TYPE_PENDING = 'pending';

    /**
     * @var string
     */
    const OPTION_TYPE_REFUND = 'refund';

    /**
     * @var string
     */
    const OPTION_TYPE_PAID = 'paid';

    /**
     * @var integer
     */
    const OPTION_DAYS_DEFAULT = 5;

    /**
     * @var boolean
     */
    const OPTION_AUTO_DEFAULT = true;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected $startAtEmailSeen = false;

    protected function configure()
    {
        $this
            ->setName('Email:tenant')
            ->setDescription('Send emails by request to tenants')
            ->addOption(
                self::OPTION_TYPE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Select email/query type',
                self::OPTION_TYPE_DEFAULT
            )
            ->addOption(
                self::OPTION_DAYS,
                null,
                InputOption::VALUE_OPTIONAL,
                'Days before due date',
                self::OPTION_DAYS_DEFAULT
            )
            ->addOption(
                self::OPTION_AUTO,
                null,
                InputOption::VALUE_NONE,
                'Autopay true/false'
            )
            ->addOption(
                self::OPTION_DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Dont send emails, just list addresses. (not supported with --late option)'
            )
            ->addOption(
                self::OPTION_START_AT_EMAIL,
                null,
                InputOption::VALUE_OPTIONAL,
                'Skip sending emails until you match this email address, then send all that follow.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        //@TODO find best way for this implementation
        //For this functional need show unit which was removed
        $this->getContainer()->get('soft.deleteable.control')->disable();
        $type = $input->getOption('type');
        $days = $input->getOption('days');
        $auto = $input->getOption('auto');
        $date = new DateTime();
        $mailer = $this->getContainer()->get('project.mailer');
        $doctrine = $this->getContainer()->get('doctrine');
        $shiftedDate = clone $date;
        $shiftedDate->modify("+{$days} days");
        switch ($type) {
            case self::OPTION_TYPE_DEFAULT:
                if ($auto) {//Email:tenant --auto=true
                    // Story-1544
                    $repo = $doctrine->getRepository('RjDataBundle:Payment');
                    $payments = $repo->getActivePayments($shiftedDate);
                    $output->write('Start processing auto payment contracts');
                    /** @var Payment $payment */
                    foreach ($payments as $payment) {
                        $contract = $payment->getContract();
                        $tenant = $contract->getTenant();
                        $this->sendPaymentDueEmail($mailer, $tenant, $contract, $payment->getType());
                        $doctrine->getManager()->detach($contract);
                        $doctrine->getManager()->detach($tenant);
                        $doctrine->getManager()->detach($payment);
                    }
                    $output->write("\nFinished command \"Email:tenant --auto\"");
                } else {//Email:tenant
                    // Story-1542
                    $contractRepository = $doctrine->getRepository('RjDataBundle:Contract');
                    $contracts = $contractRepository->getPotentialLateContract($shiftedDate);
                    $output->write('Start processing non auto contracts');
                    /** @var $contract Contract */
                    foreach ($contracts as $contract) {
                        $tenant = $contract->getTenant();
                        $this->sendPaymentDueEmail($mailer, $tenant, $contract);
                        $doctrine->getManager()->detach($tenant);
                        $doctrine->getManager()->detach($contract);
                    }
                    $output->writeln("\nOK");
                }
                break;
            case self::OPTION_TYPE_LATE:
                /** @var ContractRepository $repo */
                $repo = $doctrine->getRepository('RjDataBundle:Contract');
                $contracts = $repo->getLateContracts($days);
                $output->write('Start processing late contracts');
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    $tenant = $contract->getTenant();
                    $mailer->sendRjTenantLateContract($tenant, $contract, $days);
                    $doctrine->getManager()->detach($contract);
                    $output->write('.');
                }
                $output->writeln('OK');
                break;
        }
    }

    protected function sendPaymentDueEmail($mailer, $tenant, $contract, $paymentType = null)
    {
        if ($this->shouldSendEmail($tenant)) {
            $mailer->sendRjPaymentDue($tenant, $contract->getHolding(), $contract, $paymentType);
            $this->output->write("\n" . $tenant->getId() . " : " . $tenant->getEmail() ." - sent", OutputInterface::VERBOSITY_VERBOSE);
            $this->output->write('.');
        }
    }

    protected function shouldSendEmail($tenant)
    {
        // if a start-at email specified, skip all tenants until we see it
        $startAtEmail = $this->input->getOption(self::OPTION_START_AT_EMAIL);
        if ($startAtEmail) {
            if ($startAtEmail === $tenant->getEmail()) {
                $this->startAtEmailSeen = true;
            }

            if (!$this->startAtEmailSeen) {
                $this->outputSkipMessage($tenant, "SKIP");

                return false;
            }
        }

        // don't send if we are in "Dry Run" mode
        if ($this->input->getOption(self::OPTION_DRY_RUN)) {
            $this->outputSkipMessage($tenant, "DRYRUN MODE -- Will Send");

            return false;
        }

        // send!!
        return true;
    }

    protected function outputSkipMessage($tenant, $reason)
    {
        $this->output->write("\n" . $tenant->getId() . " : " . $tenant->getEmail() . " <" . $reason .">");
    }
}
