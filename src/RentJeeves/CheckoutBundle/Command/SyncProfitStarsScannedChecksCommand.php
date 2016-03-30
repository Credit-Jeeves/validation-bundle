<?php
namespace RentJeeves\CheckoutBundle\Command;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorProfitStarsRdc;
use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncProfitStarsScannedChecksCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('renttrack:profit-stars:sync-scanned-checks')
            ->setDescription('Syncs Profit Stars batches with scanned checks.')
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, 'Date format Y-m-d ')
            ->addOption('group-id', null, InputOption::VALUE_OPTIONAL, 'Group ID');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getLogger()->info(sprintf('Starting command %s', $this->getName()));

        $date = $this->transformToDateTime($input->getOption('date'));
        $groups = $this->getGroupsForSync($input->getOption('group-id'));

        $paymentProcessor = $this->getPaymentProcessorProfitStarsRdc();
        foreach ($groups as $group) {
            $this->getLogger()->info(sprintf('Sync scanned checks for Group#%d', $group->getId()));
            $paymentProcessor->loadScannedChecks($group, $date);
        }

        $this->getLogger()->info(sprintf('Finished command %s', $this->getName()));
    }

    /**
     * @param int $groupId
     * @return Group[]
     */
    protected function getGroupsForSync($groupId)
    {
        $groups = [];
        if (null !== $groupId) {
            if (null === $group = $this->getGroup($groupId)) {
                throw new \InvalidArgumentException(sprintf('Group with id#%s not found', $groupId));
            }
            if ($this->isGroupEnabledForProfitStars($group)) {
                $groups[] = $group;
            }
        } else {
            $groups = $this->getProfitStarsEnabledGroups();
        }

        return $groups;
    }

    /**
     * @param int $groupId
     * @return Group|null
     */
    protected function getGroup($groupId)
    {
        return $this->getEntityManager()->getRepository('DataBundle:Group')->find($groupId);
    }

    /**
     * @return Group[]
     */
    protected function getProfitStarsEnabledGroups()
    {
        return $this->getEntityManager()->getRepository('DataBundle:Group')->getProfitStarsEnabledGroups();
    }

    /**
     * @return PaymentProcessorProfitStarsRdc
     */
    protected function getPaymentProcessorProfitStarsRdc()
    {
        return $this->getContainer()->get('payment_processor.profit_stars.rdc');
    }

    /**
     * @param string|null $date
     * @return \DateTime
     * @throws \InvalidArgumentException
     */
    protected function transformToDateTime($date = null)
    {
        if (null !== $date) {
            if ($this->isValidDate($date)) {
                return new \DateTime($date);
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Date %s is incorrect. Check the format!', $date)
                );
            }
        }

        return new \DateTime();
    }

    /**
     * @param string $date
     * @return bool
     */
    protected function isValidDate($date)
    {
        $targetDate = \DateTime::createFromFormat('Y-m-d', $date);

        return $targetDate && $targetDate->format('Y-m-d') === $date;
    }

    /**
     * @param Group $group
     * @return bool
     */
    protected function isGroupEnabledForProfitStars(Group $group)
    {
        $profitStarsSettings = $group->getHolding()->getProfitStarsSettings();

        return null !== $profitStarsSettings && null !== $profitStarsSettings->getMerchantId();
    }
}
