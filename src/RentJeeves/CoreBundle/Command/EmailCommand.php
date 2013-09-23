<?php
namespace RentJeeves\CoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use RentJeeves\DataBundle\Enum\PaymentType;
use CreditJeeves\DataBundle\Enum\UserType;

class EmailCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    const OPTION_USER = 'user';

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
            ->setName('Email:start')
            ->setDescription('Send emails by request')
            ->addOption(
                self::OPTION_USER,
                null,
                InputOption::VALUE_OPTIONAL,
                'Select user type',
                UserType::TENANT
            )
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
        $user = $input->getOption('user');
        $type = $input->getOption('type');
        $days = $input->getOption('days');
        $auto = $input->getOption('auto');
        $mailer = $this->getContainer()->get('creditjeeves.mailer');
        $doctrine = $repo = $this->getContainer()->get('doctrine');
        switch ($user) {
            case UserType::TENANT:
                switch ($type) {
                    case self::OPTION_TYPE_DEFAULT:
                        if ($auto) { //Email:start --auto
                            // Story-1544
                            $repo = $doctrine->getRepository('RjDataBundle:Payment');
                            
                            $output->writeln('Story-1544');
                        } else { //Email:start
                            // Story-1542
                            $output->writeln('Story-1542');
                        }
                        break;
                }
                break;
            case UserType::LANDLORD:
                switch ($type) {
                    case self::OPTION_TYPE_INVITED: //Email:start --user=landlord --type=invited
                        // Story-1553
                        $output->writeln('Story-1553');
                        break;
                    case self::OPTION_TYPE_PENDING: //Email:start --user=landlord --type=pending
                        // Story-2042
                        $output->writeln('Story-2042');
                        break;
                    case self::OPTION_TYPE_REFUND: //Email:start --user=landlord --type=refund
                        // Story-1560
                        $output->writeln('Story-1560');
                        break;
                    case self::OPTION_TYPE_PAID: //Email:start --user=landlord --type=paid
                        // Story-1555
                        $output->writeln('Story-1555');
                        break;
                }
                break;
        }
    }
}
