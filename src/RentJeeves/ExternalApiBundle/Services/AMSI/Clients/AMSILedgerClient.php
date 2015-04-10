<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI\Clients;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\ExternalApiBundle\Model\AMSI\Payments;
use RentJeeves\ExternalApiBundle\Model\AMSI\Payment;

class AMSILedgerClient extends AMSIBaseClient
{
    const SUCCESSFUL_RESPONSE_CODE = 0;

    /**
     * @param Order $order
     * @return bool
     */
    public function postPayment(Order $order)
    {
        try {
            $parameters = $this->getParametersForAddPaymentCall($order);
            $rawResponse = $this->sendRequest('AddPayment', $parameters);
            /** @var Payments $paymentsResponse */
            $paymentsResponse = $this->serializer->deserialize(
                $rawResponse,
                'RentJeeves\ExternalApiBundle\Model\AMSI\Payments',
                'xml',
                $this->getDeserializationContext(['addPaymentResponse'])
            );
            if ($paymentsResponse instanceof Payments && isset($paymentsResponse->getPayments()[0])) {
                /** @var Payment $resultPayment */
                $resultPayment = $paymentsResponse->getPayments()[0];
                if (self::SUCCESSFUL_RESPONSE_CODE == $resultPayment->getErrorCode()) {
                    return true;
                } else {
                    $this->logger->alert(sprintf(
                        'AMSI: Failed posting order(ID#%d). Got error code %d, error description %s',
                        $order->getId(),
                        $resultPayment->getErrorCode(),
                        $resultPayment->getErrorDescription()
                    ));
                }
            } else {
                $this->logger->alert(sprintf(
                    'AMSI: Failed posting order(ID#%d). Cannot deserialize response.',
                    $order->getId()
                ));
            }
        } catch (\Exception $e) {
            $this->logger->alert(sprintf(
                'AMSI: Failed posting order(ID#%d). Got exception %s',
                $order->getId(),
                $e->getMessage()
            ));
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getLoginCredentials()
    {
        return parent::getLoginCredentials() + ['preliminary' => 0];
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getParametersForAddPaymentCall(Order $order)
    {
        $contract = $order->getContract();
        $externalUnitId = $contract->getUnit()->getUnitMapping()->getExternalUnitId();
        list($propertyId, $buildingId, $unitId) = explode('|', $externalUnitId);
        if (!($propertyId && $buildingId && $unitId)) {
            throw new \RuntimeException(sprintf(
                'AMSI: Cannot post order #%d: external unit mapping (%s) invalid',
                $order->getId(),
                $externalUnitId
            ));
        }

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
                ['XMLData' => new \SoapVar($xmlData, XSD_ANYXML)]
            ),
        ];

        return $parameters;
    }
}
