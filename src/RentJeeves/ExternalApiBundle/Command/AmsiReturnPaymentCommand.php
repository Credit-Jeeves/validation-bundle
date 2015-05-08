<?php

namespace RentJeeves\ExternalApiBundle\Command;

use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILedgerClient;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AmsiReturnPaymentCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('api:accounting:amsi:return-payment')
            ->addArgument('orderId', InputArgument::REQUIRED)
            ->addOption('jms-job-id', null, InputOption::VALUE_OPTIONAL, 'Job ID')
            ->setDescription('Return or reverse an existing payment in eSite that was added with the AddPayment.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderRepository = $this->getEntityManager()->getRepository('DataBundle:Order');
        if (false == $order = $orderRepository->find($input->getArgument('orderId'))) {
            throw new \InvalidArgumentException(
                sprintf('Order %s not found when trying to return to AMSI', $input->getArgument('orderId'))
            );
        }

        $settings = $order->getContract()->getHolding()->getAmsiSettings();

        $amsiLedgerClient = $this->createAmsiLedgerClient($settings);

        if (true === $amsiLedgerClient->returnPayment($order)) {
            $amsiLedgerClient->updateSettlementData(
                $order->getCompleteTransaction()->getTransactionId(),
                $order->getContract()->getGroupId(),
                $this->getSettlementAmount($order->getCompleteTransaction()),
                $this->getSettlementDate($order->getReversedTransaction())
            );
        }
    }

    /**
     * @param Transaction $reversedTransaction
     *
     * @return \DateTime
     */
    protected function getSettlementDate(Transaction $reversedTransaction)
    {
        return $this->getSettlementData()
            ->getSettlementDate($reversedTransaction->getBatchDate(), $reversedTransaction->getDepositDate());
    }

    /**
     * @param Transaction $completeTransaction
     *
     * @return double
     */
    protected function getSettlementAmount(Transaction $completeTransaction)
    {
        return $completeTransaction->getDepositDate() ? $completeTransaction->getOrder()->getSum() : 0;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @param SettingsInterface $settings
     *
     * @return AMSILedgerClient
     */
    protected function createAmsiLedgerClient(SettingsInterface $settings)
    {
        $clientFactory = $this->getContainer()->get('soap.client.factory');

        return $clientFactory->getClient($settings, SoapClientEnum::AMSI_LEDGER);
    }

    /**
     * @return \RentJeeves\ExternalApiBundle\Services\AMSI\SettlementData
     */
    protected function getSettlementData()
    {
        return $this->getContainer()->get('accounting.amsi_settlement');
    }
}
