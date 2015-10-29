<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor;

use RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorFactory;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\BaseTestCase;

class PaymentProcessorFactoryCase extends BaseTestCase
{
    /**
     * @var PaymentProcessorFactory
     */
    protected $factory;

    public function setUp()
    {
        $this->factory = new PaymentProcessorFactory();
        $heartland = $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorHeartland',
            [],
            [],
            '',
            false
        );
        $aciCollectPay = $this->getMock(
            '\RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciCollectPay',
            [],
            [],
            '',
            false
        );
        $this->factory->setPaymentProcessors($heartland, $aciCollectPay);
    }

    /**
     * @return array
     */
    public function paymentProcessorsDataProvider()
    {
        return [
            [PaymentProcessor::ACI, '\RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorAciCollectPay'],
            [PaymentProcessor::HEARTLAND, '\RentJeeves\CheckoutBundle\PaymentProcessor\PaymentProcessorHeartland'],
        ];
    }

    /**
     * @param $paymentProcessorType
     * @param $paymentProcessorClass
     *
     * @test
     * @dataProvider paymentProcessorsDataProvider
     */
    public function shouldGetPaymentProcessorByPaymentAccountForCorrectPaymentProcessorType(
        $paymentProcessorType,
        $paymentProcessorClass
    ) {
        $paymentAccount = $this->getMock('\RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface');
        $paymentAccount
            ->expects($this->any())
            ->method('getPaymentProcessor')
            ->will($this->returnValue($paymentProcessorType));

        $paymentProcessor = $this->factory->getPaymentProcessorByPaymentAccount($paymentAccount);

        $this->assertInstanceOf(
            $paymentProcessorClass,
            $paymentProcessor,
            sprintf('Incorrect payment processor, should be "%s"', $paymentProcessorClass)
        );
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidArgumentException
     * @expectedExceptionMessage Unknown processor type "IncorrectType" for payment account "TestName" with id = "1"
     */
    public function shouldThrowExceptionPaymentProcessorByPaymentAccountForIncorrectPaymentProcessorType()
    {
        $paymentAccount = $this->getMock('\RentJeeves\CheckoutBundle\PaymentProcessor\PaymentAccountInterface');
        $paymentAccount
            ->expects($this->any())
            ->method('getPaymentProcessor')
            ->will($this->returnValue('IncorrectType'));
        $paymentAccount
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('TestName'));
        $paymentAccount
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $this->factory->getPaymentProcessorByPaymentAccount($paymentAccount);
    }
}
