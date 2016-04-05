<?php

namespace RentJeeves\CheckoutBundle\Command;

use RentJeeves\CoreBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProfitStarsReportSynchronizeCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('renttrack:payment:report:synchronize:profit-stars')
            ->setDescription('Synchronize ProfitStars reports');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start sync ProfitStars reports.');

        $startDate = new \DateTime();
        $startDate->setTime(0, 0, 0);

        $endDate = clone $startDate;
        $endDate->setTime(23,59,59);

        foreach ($this->getHoldingRepository()->findAllHoldingsWithProfitStarsSetting() as $row) {
            $this->getReportSynchronizer()->sync($row['merchantName'], $row['merchantId'], $startDate, $endDate);
        }
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\HoldingRepository
     */
    protected function getHoldingRepository()
    {
        return $this->getEntityManager()->getRepository('DataBundle:Holding');
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\ProfitStarsReportSynchronizer
     */
    protected function getReportSynchronizer()
    {
        return $this->getContainer()->get('payment_processor.profit_stars.report_synchronizer');
    }
}
