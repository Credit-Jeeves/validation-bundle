<?php
namespace RentJeeves\CoreBundle\Command;

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
    const OPTION_TYPE_DEFAULT = 'pay';

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
        $doctrine = $repo = $this->getContainer()->get('doctrine');
        switch ($type) {
            case self::OPTION_TYPE_INVITED: //Email:landlord
                // Story-1553
                $output->writeln('Story-1553');
                break;
            case self::OPTION_TYPE_PENDING: //Email:landlord --type=pending
                $repo = $doctrine->getRepository('RjDataBundle:Contract');
                $contracts = $repo->findByStatus(ContractStatus::PENDING);
                foreach ($contracts as $contract) {
                    $holding = $contract->getHolding();
                    $landlords = $doctrine->getRepository('DataBundle:User')->findBy(array('holding' => $holding));
                    foreach ($landlords as $landlord) {
                        if ($isSuperAdmin = $landlord->getIsSuperAdmin()) {
                            $mailer->sendPendingContractToLandlord($landlord, $contract->getTenant(), $contract);
                        }
                    }
                    
                }
                $output->writeln('Story-2042');
                break;
            case self::OPTION_TYPE_REFUND: //Email:landlord
                // Story-1560
                $output->writeln('Story-1560');
                break;
            case self::OPTION_TYPE_DEFAULT: //Email:landlord
                // Story-1555
                $output->writeln('Story-1555');
                break;
        }
    }
}
