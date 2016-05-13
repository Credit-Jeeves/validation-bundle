<?php

namespace RentJeeves\ExternalApiBundle\Services\AMSI\Clients;

use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\ExternalApiBundle\Model\AMSI\Batches;
use RentJeeves\ExternalApiBundle\Model\AMSI\EdexSettlement;
use RentJeeves\ExternalApiBundle\Model\AMSI\Payments;
use RentJeeves\ExternalApiBundle\Model\AMSI\Payment;
use RentJeeves\ExternalApiBundle\Model\AMSI\ReturnPayment;
use RentJeeves\ExternalApiBundle\Model\AMSI\ReturnPayments;
use RentJeeves\ExternalApiBundle\Services\AMSI\Enum\ReversalReasonEnum;

class AMSILedgerClient extends AMSIBaseClient
{
    const SUCCESSFUL_RESPONSE_CODE = 0;
    const SETTLEMENT_APPROVAL_CODE = 'RentTrack';

    /**
     * @param Order $order
     *
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
            if ($paymentsResponse instanceof Payments && $paymentsResponse->getPayments()->first()) {
                /** @var Payment $resultPayment */
                $resultPayment = $paymentsResponse->getPayments()->first();
                if (self::SUCCESSFUL_RESPONSE_CODE == $resultPayment->getErrorCode()) {
                    return true;
                } else {
                    // TODO: replace alert with exception. See RT-1449
                    $this->logger->alert(sprintf(
                        'AMSI: Failed posting order(ID#%d). Got error code %d, error description %s',
                        $order->getId(),
                        $resultPayment->getErrorCode(),
                        $resultPayment->getErrorDescription()
                    ));
                }
            } else {
                // TODO: replace alert with exception. See RT-1449
                $this->logger->alert(sprintf(
                    'AMSI: Failed posting order(ID#%d). Cannot deserialize response.',
                    $order->getId()
                ));
            }
        } catch (\Exception $e) {
            // TODO: replace alert with exception. See RT-1449
            $this->logger->alert(sprintf(
                'AMSI: Failed posting order(ID#%d). Got exception %s',
                $order->getId(),
                $e->getMessage()
            ));
        }

        return false;
    }

    /**
     * @param integer   $batchId
     * @param integer   $clientMerchantId
     * @param float     $settlementAmount
     * @param \DateTime $settlementDate
     *
     * @return bool
     */
    public function updateSettlementData($batchId, $clientMerchantId, $settlementAmount, \DateTime $settlementDate)
    {
        $this->logger->debug(sprintf(
            'AMSI: updateSettlementData with params: batchID %d, groupID %d, amount %d, date %s',
            $batchId,
            $clientMerchantId,
            $settlementAmount,
            $settlementDate->format('m/d/Y')
        ));

        try {
            $edex = new EdexSettlement();
            $edex->setExternalJnlNo($batchId);
            $edex->setClientMerchantId($clientMerchantId);
            $edex->setSettlementAmount($settlementAmount);
            $edex->setSettlementDate($settlementDate);
            $edex->setApprovalCode(self::SETTLEMENT_APPROVAL_CODE);

            $xmlData = SerializerXmlHelper::removeStandartHeaderXml(
                $this->serializer->serialize(
                    $edex,
                    'xml',
                    $this->getSerializationContext(['updateSettlementData'])
                )
            );
            $xmlData = SerializerXmlHelper::addCDataToString($xmlData);
            $xmlData = SerializerXmlHelper::addTagWithNameSpaceToString('XMLData', 'ns1', $xmlData);

            $parameters = [
                'UpdateSettlementData' => array_merge(
                    $this->getLoginCredentials(),
                    ['XMLData' => new \SoapVar($xmlData, XSD_ANYXML)]
                ),
            ];

            $rawResponse = $this->sendRequest('UpdateSettlementData', $parameters);
            $batchesResponse = $this->serializer->deserialize(
                $rawResponse,
                'RentJeeves\ExternalApiBundle\Model\AMSI\Batches',
                'xml',
                $this->getDeserializationContext(['updateSettlementDataResponse'])
            );
            if ($batchesResponse instanceof Batches && $edexResponse = $batchesResponse->getEdex()) {
                if (self::SUCCESSFUL_RESPONSE_CODE == $edexResponse->getErrorCode()) {
                    return true;
                } else {
                    $this->logger->alert(sprintf(
                        'AMSI: Failed updateSettlementData for batchID #%d. Got error code %d, error description %s',
                        $batchId,
                        $edexResponse->getErrorCode(),
                        $edexResponse->getErrorDescription()
                    ));
                }
            } else {
                $this->logger->emergency(sprintf(
                    'AMSI: Failed updateSettlementData for batchID #%d. Cannot deserialize response.',
                    $batchId
                ));
            }
        } catch (\Exception $e) {
            $this->logger->emergency(sprintf(
                'AMSI: Failed updateSettlementData for batchID #%d. Got exception %s',
                $batchId,
                $e->getMessage()
            ));
        }

        return false;
    }

    /**
     * @param Order $order
     *
     * @return boolean
     */
    public function returnPayment(Order $order)
    {
        $payment = new ReturnPayment();

        $payment->setClientTransactionId($order->getCompleteTransaction()->getTransactionId());
        $payment->setReason(ReversalReasonEnum::getReasonByOrder($order));
        $payment->setClientJnlNo(preg_replace('/\D/', '', $order->getCompleteTransaction()->getBatchId()));
        $payment->setDescription($order->getReversedTransaction()->getMessages());

        $payments = new ReturnPayments();
        $payments->addPayment($payment);

        try {
            $xmlData = SerializerXmlHelper::removeStandartHeaderXml(
                $this->serializer->serialize(
                    $payments,
                    'xml',
                    $this->getSerializationContext(['returnPayment'])
                )
            );
            $xmlData = SerializerXmlHelper::addCDataToString($xmlData);
            $xmlData = SerializerXmlHelper::addTagWithNameSpaceToString('XMLData', 'ns1', $xmlData);

            $parameters = [
                'ReturnPayment' => array_merge(
                    $this->getLoginCredentials(),
                    ['XMLData' => new \SoapVar($xmlData, XSD_ANYXML)]
                ),
            ];

            $rawResponse = $this->sendRequest('ReturnPayment', $parameters);
            $paymentsResponse = $this->serializer->deserialize(
                $rawResponse,
                'RentJeeves\ExternalApiBundle\Model\AMSI\ReturnPayments',
                'xml',
                $this->getDeserializationContext(['returnPaymentResponse'])
            );

            if ($paymentsResponse instanceof ReturnPayments && $paymentsResponse->getPayments()->first()) {
                /** @var Payment $resultPayment */
                $resultPayment = $paymentsResponse->getPayments()->first();
                if (self::SUCCESSFUL_RESPONSE_CODE == $resultPayment->getErrorCode()) {
                    return true;
                } else {
                    $this->logger->alert(sprintf(
                        'AMSI: Failed when trying to return order(ID#%d). Got error code %d, error description %s',
                        $order->getId(),
                        $resultPayment->getErrorCode(),
                        $resultPayment->getErrorDescription()
                    ));
                }
            } else {
                $this->logger->alert(sprintf(
                    'AMSI: Failed when trying to return order(ID#%d). Cannot deserialize response.',
                    $order->getId()
                ));
            }
        } catch (\Exception $e) {
            $this->logger->alert(sprintf(
                'AMSI: Failed when trying to return order(ID#%d). Got exception %s',
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
        $payment->setClientJnlNo(preg_replace('/\D/', '', $order->getCompleteTransaction()->getBatchId()));
        $payment->setClientMerchantId($contract->getGroup()->getId());
        $payment->setClientTransactionDate($order->getCreatedAt());
        $payment->setClientTransactionId($order->getCompleteTransaction()->getTransactionId());

        switch ($order->getPaymentType()) {
            case OrderPaymentType::CARD:
                $payment->setPaymentType('P');
                break;
            case OrderPaymentType::BANK:
                $payment->setPaymentType('C');
                break;
            case OrderPaymentType::SCANNED_CHECK:
                $payment->setPaymentType('C');
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'We can\'t post this transaction, because we send just card and bank, not: %s',
                        $order->getPaymentType()
                    )
                );
        }

        $payments = new Payments();
        $payments->addPayment($payment);

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
