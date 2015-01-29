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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
                        $mailer->sendRjPaymentDue($tenant, $contract->getHolding(), $contract, $payment->getType());
                        $doctrine->getManager()->detach($payment);
                        $output->write('.');
                    }
                    $output->write('Finished command "Email:tenant --auto"');
                } else {//Email:tenant
                    // Story-1542
                    $contractRepository = $doctrine->getRepository('RjDataBundle:Contract');
                    $contracts = $contractRepository->getPotentialLateContract($shiftedDate);
                    $output->write('Start processing non auto contracts');
                    /** @var $contract Contract */
                    foreach ($contracts as $contract) {
                        $tenant = $contract->getTenant();
                        $mailer->sendRjPaymentDue($tenant, $contract->getHolding(), $contract);
                        $doctrine->getManager()->detach($contract);
                        $output->write('.');
                    }
                    $output->writeln('OK');
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
}
