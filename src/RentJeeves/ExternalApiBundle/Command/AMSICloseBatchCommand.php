<?php

namespace RentJeeves\ExternalApiBundle\Command;

use CreditJeeves\DataBundle\Entity\Holding;
use Monolog\Logger;
use RentJeeves\CheckoutBundle\Payment\BusinessDaysCalculator;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\ExternalApiBundle\Services\AMSI\Clients\AMSILedgerClient;
use RentJeeves\ExternalApiBundle\Services\AMSI\SettlementData;
use RentJeeves\ExternalApiBundle\Services\ClientsEnum\SoapClientEnum;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AMSICloseBatchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('api:accounting:amsi:close-batches')
            ->setDescription(
                'Send request for close all opened batches.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime();
        /** @var Logger $logger */
        $logger = $this->getContainer()->get('logger');
        $logger->debug(sprintf('Trying to close AMSI batches for the date: %s', $date->format('m/d/Y')));

        try {
            /** @var SettlementData $settlementData */
            $settlementData = $this->getContainer()->get('accounting.amsi_settlement');

            /** @var Holding $holding */
            foreach ($this->getAMSIHoldings() as $holding) {
                $logger->debug(sprintf(
                    'Closing AMSI batches for holding #%d: %s',
                    $holding->getId(),
                    $holding->getName()
                ));
                /** @var AMSILedgerClient $apiClient */
                $apiClient = $this->getContainer()->get('soap.client.factory')
                    ->getClient($holding->getAmsiSettings(), SoapClientEnum::AMSI_LEDGER);
                $batches = $settlementData->getBatchesToClose($date, $holding);
                foreach ($batches as $batch) {
                    $settlementDate = $this->getSettlementDate($batch['batchDate'], $batch['depositDate']);
                    $apiClient->updateSettlementData(
                        $batch['batchId'],
                        $batch['groupId'],
                        $batch['amount'],
                        $settlementDate
                    );
                }
            }
        } catch (\Exception $e) {
            $logger->alert(sprintf(
                'AMSI: Failed closing batches. Got exception %s',
                $e->getMessage()
            ));
        }
    }

    /**
     * @return array
     */
    protected function getAMSIHoldings()
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager')
            ->getRepository('DataBundle:Holding')
            ->findAllByApiIntegration(ApiIntegrationType::AMSI);
    }

    /**
     * @param \DateTime $batchDate
     * @param \DateTime $depositDate
     * @return \DateTime
     */
    protected function getSettlementDate(\DateTime $batchDate = null, \DateTime $depositDate = null)
    {
        if ($depositDate) {
            return $depositDate;
        }

        $date = new \DateTime();
        if ($batchDate) {
            $date = $batchDate;
        }

        return BusinessDaysCalculator::getBusinessDate($date, 3); // Amount of days to get payment deposited = 3
    }
}
