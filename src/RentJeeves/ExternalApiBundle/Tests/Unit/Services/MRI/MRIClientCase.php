<?php
namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services\MRI;

use RentJeeves\ExternalApiBundle\Services\MRI\MRIClient;
use RentJeeves\TestBundle\Mocks\CommonSystemMocks;

use \DateTime;

class MRIClientCase extends \PHPUnit_Framework_TestCase
{
    protected $systemsMocks;
    protected $exceptionCatcherMock;
    protected $serializerMock;
    protected $loggerMock;

    protected function setUp()
    {
        $this->systemsMocks = new CommonSystemMocks();
        $this->exceptionCatcherMock = $this->systemsMocks->getExceptionCatcherMock();
        $this->serializerMock = $this->systemsMocks->getSerializerMock();
        $this->loggerMock = $this->systemsMocks->getLoggerMock();
    }

    /**
     * @test
     */
    public function testCreate()
    {
        new MRIClient(
            $this->exceptionCatcherMock,
            $this->serializerMock,
            $this->loggerMock
        );
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function testPostPaymentApiFailShouldAlert()
    {
        $errorMessage = "Test Generated";
        $alertRegex = "/MRI: Failed posting order\(ID#8675309\)\. Error message: {$errorMessage}.*/";
        $stubClient = $this->getMRIClientAlertMock($this->loggerMock, $alertRegex, $errorMessage);

        $externalPropertyId = "42";
        $order = $this->systemsMocks->getOrderMock(8675309);

        $stubClient->postPayment($order, $externalPropertyId);
    }

    /**
     * @param $logger
     * @param $alertRegex
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMRIClientAlertMock($logger, $alertRegex, $errorMessage)
    {
        $stubClient = $this->getMockBuilder('\RentJeeves\ExternalApiBundle\Services\MRI\MRIClient')
            ->setConstructorArgs([
                $this->exceptionCatcherMock, $this->serializerMock, $logger
            ])
            ->setMethods(["paymentToStringFormat", "sendRequest"])
            ->getMock();

        $paymentMock = $this->getPaymentMock($errorMessage);

        $stubClient->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($paymentMock));

        $logger->expects($this->once())
            ->method('alert')
            ->with($this->matchesRegularExpression($alertRegex));

        return $stubClient;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\Order
     */
    public function getPaymentMock($errorMessage)
    {
        $mockPayment = $this->getMock('\RentJeeves\ExternalApiBundle\Model\MRI\Payment', ["getEntryResponse"], [], '', false);
        $mockResponse = $this->getMock('\RentJeeves\ExternalApiBundle\Model\MRI\Response', ["getError"], [], '', false);
        $mockError = $this->getMock('\RentJeeves\ExternalApiBundle\Model\MRI\Error', ["getMessage"], [], '', false);

        $mockError->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue($errorMessage));

        $mockResponse->expects($this->once())
            ->method('getError')
            ->will($this->returnValue($mockError));

        $mockPayment->expects($this->once())
            ->method('getEntryResponse')
            ->will($this->returnValue($mockResponse));

        return $mockPayment;
    }
}
