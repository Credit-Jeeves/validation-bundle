<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\Aci\CollectPay;

use ACI\Client\CollectPay\Enum\LeastCostRouteType;
use Guzzle\Http\Exception\CurlException;
use Payum\AciCollectPay\Model\LeastCostRoute;
use Payum\AciCollectPay\Request\LeastCostRouteRequest\CheckLeastCostRoute;
use RentJeeves\CheckoutBundle\PaymentProcessor\Aci\CollectPay\LeastCostRouteManager;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class LeastCostRouteManagerCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException
     * @expectedExceptionMessage Incorrect url
     */
    public function shouldCatchAndLogExternalException()
    {
        $logger = $this->getLoggerMock();
        $logger->expects($this->once())->method('alert');

        $paymentProcessor = $this->getBaseMock('Payum\Core\Payment');
        $paymentProcessor->method('execute')->willThrowException(new CurlException('Incorrect url'));
        $payum = $this->getBaseMock('Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry');
        $payum->expects($this->any())->method('getPayment')->willReturn($paymentProcessor);

        $manager = new LeastCostRouteManager($payum, $logger);
        $manager->getLeastCostRoute('555555555555555555');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorRuntimeException
     */
    public function shouldThrowExceptionOnFailedRequest()
    {
        $logger = $this->getLoggerMock();
        $logger->expects($this->once())->method('alert');

        $manager = new LeastCostRouteManager($this->getPayumMock(LeastCostRouteType::DEBIT_CARD, false), $logger);
        $manager->getLeastCostRoute('5313298820090136');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidCardNumber
     */
    public function shouldThrowExceptionOnInvalidCard()
    {
        $logger = $this->getLoggerMock();

        $manager = new LeastCostRouteManager($this->getPayumMock(LeastCostRouteType::INVALID_CARD), $logger);
        $manager->getLeastCostRoute('0123456789101214');
    }

    /**
     * @return array
     */
    public function validCardNumberDataProvider()
    {
        return [
            ['5113298820090135', LeastCostRouteType::DEBIT_CARD, PaymentAccountType::DEBIT_CARD],
            ['4111111111111111', LeastCostRouteType::CREDIT_CARD, PaymentAccountType::CARD],
        ];
    }

    /**
     * @param string $cardNumber
     * @param string $leastCostRouteType
     * @param string $paymentAccountType
     *
     * @test
     * @dataProvider validCardNumberDataProvider
     */
    public function shouldReturnPaymentAccountTypeOnValidCard($cardNumber, $leastCostRouteType, $paymentAccountType)
    {
        $logger = $this->getLoggerMock();

        $manager = new LeastCostRouteManager($this->getPayumMock($leastCostRouteType), $logger);
        $this->assertEquals(
            $paymentAccountType,
            $manager->getLeastCostRoute('5113298820090135'),
            sprintf('For card "%s" payment account type should be "%s"', $cardNumber, $paymentAccountType)
        );
    }

    /**
     * @param string $leastCostRouteType
     * @param bool $isSuccessful
     * @return \Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPayumMock($leastCostRouteType, $isSuccessful = true)
    {
        $paymentProcessor = $this->getBaseMock('Payum\Core\Payment');
        $paymentProcessor
            ->method('execute')
            ->willReturnCallback(function (CheckLeastCostRoute $request) use ($leastCostRouteType, $isSuccessful) {
                /** @var LeastCostRoute $model */
                $model = $request->getModel();
                $model->setLeastCostRouting($leastCostRouteType);
                $request->setIsSuccessful($isSuccessful);
                $request->setModel($model);
            });
        $payum = $this->getBaseMock('Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry');
        $payum->expects($this->any())->method('getPayment')->willReturn($paymentProcessor);

        return $payum;
    }
}
