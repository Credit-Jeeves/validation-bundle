<?php
namespace RentJeeves\CoreBundle\Command;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\Bundle\DoctrineBundle\Registry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\LandlordRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\CoreBundle\Traits\DateCommon;
use CreditJeeves\DataBundle\Enum\OrderStatus;

class EmailLandlordCommand extends BaseCommand
{
    use DateCommon;

    /**
     * @var string
     */
    const OPTION_TYPE = 'type';

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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //@TODO find best way for this implementation
        //For this functional need show unit which was removed
        $this->getContainer()->get('soft.deleteable.control')->disable();
        
        $type = $input->getOption('type');
        /** @var Mailer $mailer */
        $mailer = $this->getContainer()->get('project.mailer');
        $doctrine = $this->getContainer()->get('doctrine');
        $translator = $this->getContainer()->get('translator.default');
        switch ($type) {
            case self::OPTION_TYPE_INVITED: //Email:landlord
                // Story-1553
                $output->writeln('Story-1553');
                break;
            case self::OPTION_TYPE_PENDING: //Email:landlord --type=pending
                $this->sendPendingContractEmails();
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
                    $landlords = $this->getLandlordsByHoldingAndGroup($holding, $group);
                    foreach ($landlords as $landlord) {
                        $mailer->sendTodayPayments($landlord, $payment['amount'], 'rjTodayNotPaid');
                    }
                }
                $output->writeln('Story-1560');
                break;
            case self::OPTION_TYPE_DEFAULT: //Email:landlord --type=paid
                // Story-1555
                $repo = $doctrine->getRepository('RjDataBundle:Contract');
                $payments = $repo->getPaymentsToLandlord(
                    array(
                        OrderStatus::PENDING,
                    )
                );
                foreach ($payments as $payment) {
                    $holding = $doctrine->getRepository('DataBundle:Holding')->find($payment['id']);
                    $group = $doctrine->getRepository('DataBundle:Group')->find($payment['group_id']);
                    //RT-92
                    $landlords = $this->getLandlordsByHoldingAndGroup($holding, $group);
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
                    $landlords = $this->getLandlordsByHoldingAndGroup($holding, $group);
                    foreach ($landlords as $landlord) {
                        $mailer->sendRjDailyReport($landlord, $report);
                    }
                    $doctrine->getManager()->detach($row[0]);
                }
                $output->writeln('daily report');
                break;
            case self::OPTION_TYPE_LATE:
                $output->writeln('Late contracts');
                $serializer = $this->getContainer()->get('jms_serializer');
                $output->writeln('Send to holding admins');
                $this->sendLateEmails($output, $doctrine, $mailer, $serializer);

                $output->writeln('Send to landlords');
                $this->sendLateEmails($output, $doctrine, $mailer, $serializer, false);

                $output->writeln('OK');
                break;
        }
    }

    protected function sendLateEmails(
        OutputInterface $output,
        Registry $doctrine,
        Mailer $mailer,
        Serializer $serializer,
        $holdingAdmin = true
    ) {
        /** @var LandlordRepository $repoLandlord */
        $repoLandlord = $doctrine->getRepository('RjDataBundle:Landlord');
        /** @var ContractRepository $repoContract */
        $repoContract = $doctrine->getRepository('RjDataBundle:Contract');

        $landlords = $repoLandlord->findBy(['is_super_admin' => $holdingAdmin], ['email' => 'DESC']);
        foreach ($landlords as $landlord) {
            /** @var Landlord $landlord */
            if ($holdingAdmin) {
                $lateContracts = $repoContract->getAllLateContractsByHolding($landlord->getHolding());
            } else {
                $lateContracts = $repoContract->getAllLateContractsByGroups($landlord->getAgentGroups());
            }

            $tenants = $serializer->serialize(
                $lateContracts,
                'array',
                SerializationContext::create()->setGroups(['lateEmailReport'])
            );
            if (count($tenants) > 0) {
                $mailer->sendListLateContracts($landlord, $tenants);
            }
            $output->write('.');
        }
        $output->writeln('.');
    }

    /**
     * @param Holding $holding
     * @param Group $group
     * @return Landlord[]
     */
    private function getLandlordsByHoldingAndGroup(Holding $holding, Group $group)
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

    protected function sendPendingContractEmails()
    {
        $mailer = $this->getContainer()->get('project.mailer');
        $contracts = $this->getEntityManager()->getRepository('RjDataBundle:Contract')->getLastPendingContracts();
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $landlords = $this->getLandlordsByHoldingAndGroup($contract->getHolding(), $contract->getGroup());
            foreach ($landlords as $landlord) {
                if ($landlord->getIsActive()) {
                    $mailer->sendPendingContractToLandlord($landlord, $contract);
                }
            }
        }
    }
}
