<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Parser;

use Psr\Log\LoggerInterface;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Report as AdjustmentReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Report as ResponseReport;
use RentJeeves\CheckoutBundle\PaymentProcessor\Report\PaymentProcessorReportTransaction;
use JMS\Serializer\SerializerInterface;

abstract class AbstractParser implements AciPayAnyoneParserInterface
{
    const XML_ENCODING = 'ISO-8859-1';
    const XML_VERSION = '1.0';

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($xmlData)
    {
        $transactions = [];

        try {
            $xmlData = $this->prepareXml($xmlData);
            $report = $this->deserializeXml($xmlData);
            $transactions = $this->getTransactionsFromReport($report);
        } catch (\Exception $e) {
            $this->logger->alert(sprintf('ACI: Unexpected error: %s', $e->getMessage()));
        }

        if (count($transactions) === 0) {
            $this->logger->alert('ACI: PayAnyone parser found no transactions in the XML data');
        }

        return $transactions;
    }

    /**
     * @param string $xmlData
     *
     * @return string XML
     */
    protected function prepareXml($xmlData)
    {
        $document = new \DOMDocument(self::XML_VERSION, self::XML_ENCODING);
        $document->loadXML($xmlData);
        $document->removeChild($document->firstChild); // remove "bad" line

        return $document->saveXML();
    }

    /**
     * @param string $xmlData
     *
     * @return ResponseReport|AdjustmentReport
     */
    protected function deserializeXml($xmlData)
    {
        return $this->serializer->deserialize($xmlData, $this->getDeserializationModel(), 'xml');
    }

    /**
     * @return string
     */
    abstract protected function getDeserializationModel();

    /**
     * @param ResponseReport|AdjustmentReport $report
     *
     * @return PaymentProcessorReportTransaction[]
     */
    abstract protected function getTransactionsFromReport($report);
}
