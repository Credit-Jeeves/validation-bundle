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

        $manager = new LeastCostRouteManager($this->getPayumMock(), $logger);
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

        $manager = new LeastCostRouteManager($this->getPayumMock(), $logger);
        $manager->getLeastCostRoute('5313298820090136');
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\Exception\PaymentProcessorInvalidCardNumber
     */
    public function shouldThrowExceptionOnInvalidCard()
    {
        $logger = $this->getLoggerMock();

        $manager = new LeastCostRouteManager($this->getPayumMock(), $logger);
        $manager->getLeastCostRoute('0123456789101214');
    }

    /**
     * @return array
     */
    public function validCardNumberDataProvider()
    {
        return [
            ['5113298820090135', PaymentAccountType::DEBIT_CARD],
            ['4111111111111111', PaymentAccountType::CARD],
        ];
    }

    /**
     * @param string $cardNumber
     * @param string $paymentAccountType
     *
     * @test
     * @dataProvider validCardNumberDataProvider
     */
    public function shouldReturnPaymentAccountTypeOnValidCard($cardNumber, $paymentAccountType)
    {
        $logger = $this->getLoggerMock();

        $manager = new LeastCostRouteManager($this->getPayumMock(), $logger);
        $this->assertEquals(
            $paymentAccountType,
            $manager->getLeastCostRoute($cardNumber),
            sprintf('For card "%s" payment account type should be "%s"', $cardNumber, $paymentAccountType)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry
     */
    protected function getPayumMock()
    {
        $paymentProcessor = $this->getBaseMock('Payum\Core\Payment');
        $paymentProcessor->method('execute')->willReturnCallback(function (CheckLeastCostRoute $request) {
            /** @var LeastCostRoute $model */
            $model = $request->getModel();
            switch ($model->getCardNumber()) {
                case '0123456789101214':
                    $model->setLeastCostRouting(LeastCostRouteType::INVALID_CARD);
                    break;
                case '4111111111111111':
                    $model->setLeastCostRouting(LeastCostRouteType::CREDIT_CARD);
                    break;
                case '5113298820090135':
                    $model->setLeastCostRouting(LeastCostRouteType::DEBIT_CARD);
                    break;
                case '5313298820090136':
                    $model->setLeastCostRouting(LeastCostRouteType::CREDIT_CARD);
                    $request->setIsSuccessful(false);
                    break;
                default:
                    throw new CurlException('Incorrect url');
            }
            $request->setModel($model);
        });
        $payum = $this->getBaseMock('Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry');
        $payum->expects($this->any())->method('getPayment')->willReturn($paymentProcessor);

        return $payum;
    }
}