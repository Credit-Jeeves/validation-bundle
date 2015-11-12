<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor;

use CreditJeeves\DataBundle\Entity\Order;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\FundingAccountManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\PaymentManager;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\ReportLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciCollectPay;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class PaymentProcessorAciCollectPayCase extends UnitTestBase
{
    use WriteAttributeExtensionTrait;

    /**
     * @test
     */
    public function shouldGenerateCorrectReversedBatchId()
    {
        $paymentProcessorAciCollect = new PaymentProcessorAciCollectPay(
            $this->getEnrollmentManagerMock(),
            $this->getBillingAccountManagerMock(),
            $this->getFundingAccountManagerMock(),
            $this->getPaymentManagerMock(),
            $this->getReportLoaderMock()
        );

        $order = new Order();
        $depositAccount = new DepositAccount();
        $this->writeIdAttribute($depositAccount, 'test');
        $order->setDepositAccount($depositAccount);

        $date = new \DateTime();

        $expectedResult = 'testR' . $date->format('Ymd');
        $result = $paymentProcessorAciCollect->generateReversedBatchId($order);

        $this->assertEquals($expectedResult, $result, 'Generated BatchId not equals with expected value');
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager
     */
    protected function getEnrollmentManagerMock()
    {
        return $this->getMock(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager
     */
    protected function getBillingAccountManagerMock()
    {
        return $this->getMock(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\FundingAccountManager
     */
    protected function getFundingAccountManagerMock()
    {
        return $this->getMock(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\FundingAccountManager',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\PaymentManager
     */
    protected function getPaymentManagerMock()
    {
        return $this->getMock(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\PaymentManager',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @return \RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\ReportLoader
     */
    protected function getReportLoaderMock()
    {
        return $this->getMock(
            'RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\ReportLoader',
            [],
            [],
            '',
            false
        );
    }
}
