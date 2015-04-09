<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI\Clients;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\ExternalApiBundle\Model\AMSI\Payments;
use RentJeeves\ExternalApiBundle\Model\AMSI\Payment;

class AMSILedgerClient extends AMSIBaseClient
{
    public function addPayment(Order $order)
    {
        $contract = $order->getContract();
        $externalUnitId = $contract->getUnit()->getUnitMapping()->getExternalUnitId();
        list($propertyId, $buildingId, $unitId) = explode('|', $externalUnitId);

        $payment = new Payment();

        $payment->setPropertyId($propertyId);
        $payment->setBldgId($buildingId);
        $payment->setUnitId($unitId);
        $payment->setResiId($contract->getExternalLeaseId());
        $payment->setAmount($order->getSum());
        $payment->setClientJnlNo($order->getCompleteTransaction()->getBatchId());
        $payment->setClientMerchantId($contract->getGroup()->getId());
        $payment->setClientTransactionDate($order->getCreatedAt());
        $payment->setClientTransactionId($order->getCompleteTransaction()->getTransactionId());

        $payments = new Payments();
        $payments->setPayments([$payment]);

        $xmlData = SerializerXmlHelper::removeStandartHeaderXml(
            $this->serializer->serialize(
                $payments,
                'xml',
                $this->getSerializationContext(['addPayment'])
            )
        );
        $xmlData = SerializerXmlHelper::addCDataToString($xmlData);
        $xmlData = SerializerXmlHelper::addTagWithNameSpaceToString('XMLData', 'ns1', $xmlData);

        $parameters = [
            'AddPayment' => array_merge(
                $this->getLoginCredentials(),
                ['XMLData'=> new \SoapVar($xmlData, XSD_ANYXML)]
            ),
        ];

        $rawResponse = $this->sendRequest('AddPayment', $parameters);
        $paymentsResponse = $this->serializer->deserialize(
            $rawResponse,
            'RentJeeves\ExternalApiBundle\Model\AMSI\Payments',
            'xml',
            $this->getDeserializationContext(['addPaymentResponse'])
        );

        return true;
    }

    /**
     * @return array
     */
    protected function getLoginCredentials()
    {
        return parent::getLoginCredentials() + ['preliminary' => 0];
    }
}
