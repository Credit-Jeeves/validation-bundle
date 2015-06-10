<?php

namespace RentJeeves\CheckoutBundle\Tests\PaymentProcessor\Aci\PayAnyone;

use RentJeeves\TestBundle\BaseTestCase;

class ModelsCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldDeserializeResponseFile()
    {
        $pathToFile = $this->getFileLocator()->locate('@RjCheckoutBundle/Tests/Fixtures/Aci/testResponseFile.xml');

        $document = new \DOMDocument('1.0', 'ISO-8859-1');
        $document->load($pathToFile);
        $document->removeChild($document->firstChild); // remove bad line
        $xml = $document->saveXML();
        /** @var \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Report $result */
        $result = $this->getSerializer()->deserialize(
            $xml,
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Report',
            'xml'
        );

        $this->assertCount(2, $result->getBatches());

        /** @var \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Batch $firstBatch */
        $firstBatch = $result->getBatches()->first();
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Batch',
            $firstBatch
        );
        $this->assertEquals('165641', $firstBatch->getBatchId());
        $this->assertCount(1, $firstBatch->getPayments());
        /** @var \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Payment $firstPayment */
        $firstPayment = $firstBatch->getPayments()->first();
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Payment',
            $firstPayment
        );
        $this->assertEquals('102497630', $firstPayment->getTransactionId());
        $this->assertEquals(320, $firstPayment->getAmount());
        $this->assertEquals('READY TO DISBURSE', $firstPayment->getReponseCode());
        $this->assertEquals('UNKNOWN_BILLER', $firstPayment->getResponseMessage());
        $this->assertEquals('2015-02-24', $firstPayment->getBatchCloseDate()->format('Y-m-d'));

        /** @var \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Response\Batch $secondBatch */
        $secondBatch = $result->getBatches()->next();
        $this->assertCount(6, $secondBatch->getPayments());
    }

    /**
     * @test
     */
    public function shouldDeserializeAdjustmentFile()
    {
        $pathToFile = $this->getFileLocator()->locate('@RjCheckoutBundle/Tests/Fixtures/Aci/testAdjustFile.xml');

        $document = new \DOMDocument('1.0', 'ISO-8859-1');
        $document->load($pathToFile);
        $document->removeChild($document->firstChild); // remove bad line
        $xml = $document->saveXML();
        /** @var \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Report $report */
        $report = $this->getSerializer()->deserialize(
            $xml,
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Report',
            'xml'
        );

        $this->assertEquals('2015-06-03', $report->getDepositDate()->format('Y-m-d'));
        /** @var \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Originator $originator */
        $originator = $report->getOriginator();

        $this->assertCount(2, $originator->getDepositTransactions()->getPayments());
        /** @var \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Payment $firstPayment */
        $firstPayment = $originator->getDepositTransactions()->getPayments()->first();
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Payment',
            $firstPayment
        );
        $detail = $firstPayment->getDetail();
        $this->assertEquals('97055778', $detail->getTransactionId());
        $this->assertEquals(999, $detail->getAmount());
        $this->assertEquals('', $detail->getReturnCode());

        $this->assertCount(1, $originator->getRefundedOutdatedChecks()->getPayments());
        /** @var \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Payment $firstPayment */
        $firstPayment = $originator->getRefundedOutdatedChecks()->getPayments()->first();
        $this->assertInstanceOf(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\Aci\PayAnyone\Model\Adjustment\Payment',
            $firstPayment
        );
        $detail = $firstPayment->getDetail();
        $this->assertEquals('97055769', $detail->getTransactionId());
        $this->assertEquals(999, $detail->getAmount());
        $this->assertEquals('', $detail->getReturnCode());

        $this->assertCount(1, $originator->getRefundedStoppedChecks()->getPayments());
        $this->assertCount(1, $originator->getReissuedStoppedChecks()->getPayments());
        $this->assertCount(1, $originator->getStoppedChecks()->getPayments());
    }

    /**
     * @return \Symfony\Component\Serializer\Serializer
     */
    protected function getSerializer()
    {
        return $this->getContainer()->get('serializer');
    }

    /**
     * @return \Symfony\Component\HttpKernel\Config\FileLocator
     */
    protected function getFileLocator()
    {
        return $this->getContainer()->get('file_locator');
    }
}
