<?php
namespace RentJeeves\CoreBundle\Command;

use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
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
use RentJeeves\DataBundle\Enum\ContractStatus;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use CreditJeeves\DataBundle\Entity\Order;

class EmailLandlordCommand extends ContainerAwareCommand
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
    const OPTION_TYPE_DEFAULT = 'paid';

    /**
     * @var string
     */
    const OPTION_TYPE_NFS = 'nsf';

    /**
     * @var string
     */
    const OPTION_TYPE_REPORT = 'report';

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
            ->setName('Email:landlord')
            ->setDescription('Send emails by request to landlords')
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
        $type = $input->getOption('type');
        $days = $input->getOption('days');
        $auto = $input->getOption('auto');
        $date = new \DateTime();
        $mailer = $this->getContainer()->get('project.mailer');
        $doctrine = $this->getContainer()->get('doctrine');
        $translator = $this->getContainer()->get('translator.default');
        switch ($type) {
            case self::OPTION_TYPE_INVITED: //Email:landlord
                // Story-1553
                $output->writeln('Story-1553');
                break;
            case self::OPTION_TYPE_PENDING: //Email:landlord --type=pending
                $repo = $doctrine->getRepository('RjDataBundle:Contract');
                $contracts = $repo->findByStatus(ContractStatus::PENDING);
                /**
                 * @var Contract $contract
                 */
                foreach ($contracts as $contract) {
                    $holding = $contract->getHolding();
                    $group = $contract->getGroup();
                    //RT-92
                    $landlords = $this->getLandlordByHoldingAndGroup($holding, $group);
                    /**
                     * @var Landlord $landlord
                     */
                    foreach ($landlords as $landlord) {
                        // TODO: in future we should discover another way of getting registered landlords
                        if (!$landlord->getInviteCode()) {
                            $mailer->sendPendingContractToLandlord($landlord, $contract->getTenant(), $contract);
                        }
                    }
                }
                $output->writeln('Story-2042');
                break;
            case self::OPTION_TYPE_NFS: //Email:landlord --type=nsf
                // Story-1560
                $repo = $doctrine->getRepository('RjDataBundle:Contract');
                $payments = $repo->getPaymentsToLandlord(
                    array(
                        OrderStatus::NEWONE,
                        OrderStatus::ERROR,
                        OrderStatus::CANCELLED,
                        OrderStatus::REFUNDED
                    )
                );
                foreach ($payments as $payment) {
                    $holding = $doctrine->getRepository('DataBundle:Holding')->find($payment['id']);
                    $group = $doctrine->getRepository('DataBundle:Group')->find($payment['group_id']);
                    //RT-92
                    $landlords = $this->getLandlordByHoldingAndGroup($holding, $group);
                    foreach ($landlords as $landlord) {
                        $mailer->sendTodayPayments($landlord, $payment['amount'], 'rjTodayNotPaid');
                    }
                }
                $output->writeln('Story-1560');
                break;
            case self::OPTION_TYPE_DEFAULT: //Email:landlord --type=paid
                // Story-1555
                $repo = $doctrine->getRepository('RjDataBundle:Contract');
                $payments = $repo->getPaymentsToLandlord();
                foreach ($payments as $payment) {
                    $holding = $doctrine->getRepository('DataBundle:Holding')->find($payment['id']);
                    $group = $doctrine->getRepository('DataBundle:Group')->find($payment['group_id']);
                    //RT-92
                    $landlords = $this->getLandlordByHoldingAndGroup($holding, $group);
                    foreach ($landlords as $landlord) {
                        $mailer->sendTodayPayments($landlord, $payment['amount']);
                    }
                }
                $output->writeln('Story-1555');
                break;
            case self::OPTION_TYPE_REPORT:
                $repo = $doctrine->getRepository('RjDataBundle:Contract');
                $contracts = $repo->getRentHoldings();
                foreach ($contracts as $row) {
                    $contract = $row[0];
                    $holding = $contract->getHolding();
                    $report = array(
                        $translator->trans('order.status.text.'.OrderStatus::NEWONE) =>
                            $repo->getPaymentsByStatus($holding, OrderStatus::NEWONE),
                        $translator->trans('order.status.text.'.OrderStatus::COMPLETE) =>
                            $repo->getPaymentsByStatus($holding, OrderStatus::COMPLETE),
                        $translator->trans('order.status.text.'.OrderStatus::ERROR) =>
                            $repo->getPaymentsByStatus($holding, OrderStatus::ERROR),
                        $translator->trans('order.status.text.'.OrderStatus::CANCELLED) =>
                            $repo->getPaymentsByStatus($holding, OrderStatus::CANCELLED),
                        $translator->trans('order.status.text.'.OrderStatus::REFUNDED) =>
                            $repo->getPaymentsByStatus($holding, OrderStatus::REFUNDED),
                        $translator->trans('order.status.text.'.OrderStatus::RETURNED) =>
                            $repo->getPaymentsByStatus($holding, OrderStatus::RETURNED),
                        'LATE' => $repo->getLateAmount($holding),
                    );
                    $group = $contract->getGroup();
                    //RT-92
                    $landlords = $this->getLandlordByHoldingAndGroup($holding, $group);
                    foreach ($landlords as $landlord) {
                        $mailer->sendRjDailyReport($landlord, $report);
                    }
                    $doctrine->getManager()->detach($row[0]);
                }
                $output->writeln('daily report');
                break;
            case self::OPTION_TYPE_LATE:
                $repo = $doctrine->getRepository('RjDataBundle:Contract');
                $contracts = $repo->getRentHoldings();
                $output->write('Late contracts');
                foreach ($contracts as $row) {
                    $contract = $row[0];
                    $holding = $contract->getHolding();
                    $group = $contract->getGroup();
                    //RT-92
                    $landlords = $this->getLandlordByHoldingAndGroup($holding, $group);
                    $late = $repo->getAllLateContracts($holding);
                    $tenants = array();
                    foreach ($late as $object) {
                        $item = array();
                        $object = $object[0];
                        $tenant = $object->getTenant();
                        $paidTo = $object->getPaidTo();
                        $diff = $paidTo->diff($date);
                        $item['name'] = $tenant->getFullName();
                        $item['email'] = $tenant->getEmail();
                        $item['address'] = $object->getRentAddress($object->getProperty(), $object->getUnit());
                        $item['late'] = $diff->format('%d');
                        $tenants[] = $item;
                    }
                    $doctrine->getManager()->detach($object);
                    if (count($tenants) > 0) {
                        foreach ($landlords as $landlord) {
                            $mailer->sendListLateContracts($landlord, $tenants);
                        }
                    }
                    $doctrine->getManager()->detach($row[0]);
                    $output->write('.');
                }
                $output->writeln('OK');
                break;
        }
    }

    private function getLandlordByHoldingAndGroup($holding, $group)
    {
        $result = array();
        $landlords = $this->getContainer()
            ->get('doctrine')
            ->getRepository('DataBundle:User')
            ->findBy(array('holding' => $holding));
        /**
         * @var Landlord $landlord
         */
        foreach ($landlords as $landlord) {
            if ($isSuperAdmin = $landlord->getIsSuperAdmin()) {
                $result[] = $landlord;
                continue;
            }

            $groups = $landlord->getGroups();
            if (empty($groups)) {
                continue;
            }

            if ($groups->contains($group)) {
                $result[] = $landlord;
                continue;
            }
        }

        return $result;
    }
}
