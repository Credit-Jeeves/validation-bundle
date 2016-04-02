<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\RDCClient;
use RentJeeves\DataBundle\Entity\ProfitStarsSettings;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSItemStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSRemoteDepositBatch;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\GetBatchesByDateRangeResponse;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\RemoteDepositReportingClient;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfWSBatchStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\ArrayOfInt;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSBatchStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositBatch;

class RDCClientCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /** @var string */
    protected $storeId = '765350';

    /** @var string */
    protected $storeKey = 'nAfv+O9D5V3i1Pgd3yaUxOAa2D9z';

    /** @var string */
    protected $entityId = '223586';

    /** @var \DateTime $createDate */
    protected $createDate;

    /** @var \DateTime $endDate */
    protected $endDate;

    /** @var array */
    protected $statuses = [
        WSBatchStatus::CLOSED,
        WSBatchStatus::READYFORPROCESSING,
        WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
    ];

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException
     */
    public function shouldThrowExceptionIfResponseIsNotInstanceOfGetBatchesByDateRangeResponse()
    {
        $date = $this->initDate();
        $group = $this->createGroup();

        $depositReportingClientMock = $this->getRemoteDepositReportingClientMock(null);

        $rdcClient = new RDCClient(
            $depositReportingClientMock,
            $this->getLoggerMock(),
            $this->storeId,
            $this->storeKey
        );

        $rdcClient->getBatches($group, $date, $this->statuses);
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException
     */
    public function shouldThrowExceptionIfGetBatchesByDateRangeResultIsNull()
    {
        $date = $this->initDate();
        $group = $this->createGroup();

        $depositReportingClientMock = $this->getRemoteDepositReportingClientMock(new GetBatchesByDateRangeResponse());

        $rdcClient = new RDCClient(
            $depositReportingClientMock,
            $this->getLoggerMock(),
            $this->storeId,
            $this->storeKey
        );

        $rdcClient->getBatches($group, $date, $this->statuses);
    }

    /**
     * @test
     */
    public function shouldGetBatchesResultAsNullWhenSetArrayWithoutWSRemoteDepositBatch()
    {
        $date = $this->initDate();
        $group = $this->createGroup();

        $response = new GetBatchesByDateRangeResponse();
        $arrayOfWSRemoteDepositBatch = new ArrayOfWSRemoteDepositBatch();

        $response->setGetBatchesByDateRangeResult($arrayOfWSRemoteDepositBatch);

        $depositReportingClientMock = $this->getRemoteDepositReportingClientMock($response);

        $rdcClient = new RDCClient(
            $depositReportingClientMock,
            $this->getLoggerMock(),
            $this->storeId,
            $this->storeKey
        );

        $result = $rdcClient->getBatches($group, $date, $this->statuses);

        $this->assertNull($result, 'Expected result is NULL b/c ArrayOfWSRemoteDepositBatch has no Batch');
    }

    /**
     * @test
     */
    public function shouldGetBatchesResultAsArrayWhenOnlyOneBatchIbResponse()
    {
        $date = $this->initDate();
        $group = $this->createGroup();

        $response = new GetBatchesByDateRangeResponse();
        $arrayOfWSRemoteDepositBatch = new ArrayOfWSRemoteDepositBatch();
        $arrayOfWSRemoteDepositBatch->setWSRemoteDepositBatch(new WSRemoteDepositBatch());

        $response->setGetBatchesByDateRangeResult($arrayOfWSRemoteDepositBatch);

        $depositReportingClientMock = $this->getRemoteDepositReportingClientMock($response);

        $rdcClient = new RDCClient(
            $depositReportingClientMock,
            $this->getLoggerMock(),
            $this->storeId,
            $this->storeKey
        );

        $result = $rdcClient->getBatches($group, $date, $this->statuses);
        $this->assertNotEmpty($result, 'Batches should no be empty');
        $this->assertTrue(is_array($result), 'Result should be array');
        $this->assertInstanceOf(
            WSRemoteDepositBatch::class,
            $result[0],
            'First elem in result should be WSRemoteDepositBatch'
        );
    }

    /**
     * @test
     * @expectedException \RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\Exception\ProfitStarsException
     * @expectedExceptionMessage GetItemsByBatchNumber for group#0 and batchNumber "111" returned empty response
     */
    public function shouldThrowExceptionIfGetItemsByBatchNumberReturnNull()
    {
        $group = $this->createGroup();
        $batchNumber = 111;

        $depositReportingClientMock = $this->getBaseMock(RemoteDepositReportingClient::class);
        $depositReportingClientMock
            ->expects($this->once())
            ->method('GetItemsByBatchNumber')
            ->with(
                $this->equalTo($this->storeId),
                $this->equalTo($this->storeKey),
                $this->equalTo($this->entityId),
                $this->equalTo($batchNumber),
                $this->isInstanceOf(ArrayOfWSItemStatus::class)
            )
            ->will($this->returnValue(null));

        $rdcClient = new RDCClient(
            $depositReportingClientMock,
            $this->getLoggerMock(),
            $this->storeId,
            $this->storeKey
        );

        $rdcClient->getBatchItems($group, $batchNumber, $this->statuses);
    }

    /**
     * @return Group
     */
    protected function createGroup()
    {
        $group = new Group();
        $holding = new Holding();
        $depositAccount = new DepositAccount();

        $profitStarsSettings = new ProfitStarsSettings();
        $profitStarsSettings->setHolding($holding);
        $profitStarsSettings->setMerchantId($this->entityId);

        $depositAccount->setMerchantName(1023318);
        $depositAccount->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);
        $depositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);

        $holding->setProfitStarsSettings($profitStarsSettings);
        $group->setHolding($holding);
        $group->addDepositAccount($depositAccount);

        return $group;
    }

    /**
     * @param mixed|null $returnValue
     * @return \PHPUnit_Framework_MockObject_MockObject|RemoteDepositReportingClient
     */
    protected function getRemoteDepositReportingClientMock($returnValue = null)
    {
        $depositReportingClientMock = $this->getBaseMock(RemoteDepositReportingClient::class);
        $depositReportingClientMock
            ->expects($this->once())
            ->method('GetBatchesByDateRange')
            ->with(
                $this->equalTo($this->storeId),
                $this->equalTo($this->storeKey),
                $this->equalTo($this->entityId),
                $this->isInstanceOf(ArrayOfInt::class),
                $this->equalTo($this->createDate->format('Y-m-d\TH:i:s')),
                $this->equalTo($this->endDate->format('Y-m-d\TH:i:s')),
                $this->isInstanceOf(ArrayOfWSBatchStatus::class)
            )
            ->will($this->returnValue($returnValue));

        return $depositReportingClientMock;
    }

    /**
     * @return \DateTime
     */
    protected function initDate()
    {
        $date = new \DateTime();
        $this->createDate = clone $date;
        $this->createDate->setTime(0, 0, 0);

        $this->endDate = clone $date;
        $this->endDate->setTime(23, 59, 59);

        return $date;
    }
}
