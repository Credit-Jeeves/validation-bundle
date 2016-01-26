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
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TestBundle\Traits\WriteAttributeExtensionTrait;

class PaymentProcessorAciCollectPayCase extends UnitTestBase
{
    use WriteAttributeExtensionTrait;
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldGenerateCorrectReversedBatchId()
    {
        $paymentProcessorAciCollect = new PaymentProcessorAciCollectPay(
            $this->getBaseMock('RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\EnrollmentManager'),
            $this->getBaseMock('RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\BillingAccountManager'),
            $this->getBaseMock('RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\FundingAccountManager'),
            $this->getBaseMock('RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\PaymentManager'),
            $this->getBaseMock('RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\LeastCostRouteManager'),
            $this->getBaseMock('RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\ReportLoader')
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
}
