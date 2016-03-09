<?php
namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services\ResMan;

use RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient;
use RentJeeves\TestBundle\Mocks\CommonSystemMocks;

use \DateTime;

class ResManClientCase extends \PHPUnit_Framework_TestCase
{
    protected $systemsMocks;
    protected $exceptionCatcherMock;
    protected $serializerMock;
    protected $loggerMock;
    protected $httpClient;

    protected function setUp()
    {
        $this->systemsMocks = new CommonSystemMocks();
        $this->exceptionCatcherMock = $this->systemsMocks->getExceptionCatcherMock();
        $this->serializerMock = $this->systemsMocks->getSerializerMock();
        $this->loggerMock = $this->systemsMocks->getLoggerMock();
        $this->httpClient = $this->getHttpClientMock();
    }

    /**
     * @test
     */
    public function testCreate()
    {
        new ResManClient(
            $this->exceptionCatcherMock,
            $this->serializerMock,
            $this->loggerMock,
            "integrationPartnerId",
            "apiKey",
            $this->httpClient
        );
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function testGetResidentTransactionsApiFailShouldAlert()
    {
        $alertRegex = "/ResMan: Can't get residents by externalPropertyId\(ID#42\)\. Error message: Test Generated.*/";
        $stubClient = $this->getResManClientAlertMock($alertRegex);

        $externalPropertyId = "42";
        $stubClient->getResidentTransactions($externalPropertyId);
    }

    /**
     * @test
     */
    public function testOpenBatchApiFailShouldAlert()
    {
        $alertRegex = "/ResMan: Can't open batch for externalPropertyId\(ID#42\)\. Error message: Test Generated.*/";
        $stubClient = $this->getResManClientAlertMock($alertRegex);

        $externalPropertyId = "42";
        $stubClient->openBatch($externalPropertyId, new DateTime(), "description", "accountId");
    }

    /**
     * @test
     */
    public function testCloseBatchApiFailShouldAlert()
    {
        $alertRegex = "/ResMan: Can't close batch for externalPropertyId\(ID#42\)\. Error message: Test Generated.*/";
        $stubClient = $this->getResManClientAlertMock($alertRegex);

        $externalPropertyId = "42";
        $stubClient->closeBatch('accountingBatchId', $externalPropertyId, 'accountId');
    }

    /**
     * @test
     */
    public function testAddPaymentToBatchApiFailShouldAlert()
    {
        $alertRegex = "/ResMan: Can't add payment to batch for OrderID\(ID#8675309\)\. " .
                      "Error message: Test Generated.*/";
        $stubClient = $this->getResManClientAlertMock($alertRegex);

        $externalPropertyId = "42";
        $order = $this->systemsMocks->getOrderMock(8675309);
        $stubClient->addPaymentToBatch($order, $externalPropertyId);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\Order
     */
    protected function getSettingsMock()
    {
        return $this->getMock('\RentJeeves\DataBundle\Entity\ResManSettings', [], [], '', false);
    }

    /**
     * @param $logger
     * @param $alertRegex
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResManClientAlertMock($alertRegex)
    {
        $stubClient = $this->getMockBuilder('\RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient')
            ->setConstructorArgs(
                [
                    $this->exceptionCatcherMock,
                    $this->serializerMock,
                    $this->loggerMock,
                    "integrationPartnerId",
                    "apiKey",
                    $this->httpClient
                ]
            )
            ->setMethods(["sendRequest", "manageResponse", "getResidentTransactionXml", "getSettings"])
            ->getMock();

        $stubClient->method('sendRequest')
            ->will($this->throwException(new \Exception("Test Generated")));

        $stubClient->method('getSettings')
            ->will($this->returnValue($this->getSettingsMock()));

        $this->loggerMock->expects($this->once())
            ->method('alert')
            ->with($this->matchesRegularExpression($alertRegex));

        return $stubClient;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Guzzle\Http\Client
     */
    protected function getHttpClientMock()
    {
        return $this->getMock('\Guzzle\Http\Client', [], [], '', false);
    }
}
