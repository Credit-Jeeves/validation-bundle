<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\ApiBundle\Services\Encoders\Skip32IdEncoder;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\RDCClient;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\RemoteDepositLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ScannedCheckTransformer;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSBatchStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSItemStatus;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositBatch;
use RentTrack\ProfitStarsClientBundle\RemoteDepositReporting\Model\WSRemoteDepositItem;

class RemoteDepositLoaderCase extends UnitTestBase
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldNotCreateOrderIfDepositItemIsDeleted()
    {
        $date = new \DateTime();
        $group = new Group();
        $batchNumber = 111;

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch->setBatchNumber($batchNumber);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                ]
            )
            ->will($this->returnValue([$remoteBatch]));
        $depositItem = new WSRemoteDepositItem();
        $depositItem->setDeleted(true);

        $rdcClientMock
            ->expects($this->once())
            ->method('getBatchItems')
            ->with(
                $group,
                $batchNumber,
                [
                    WSItemStatus::CREATED,
                    WSItemStatus::APPROVED,
                    WSItemStatus::SENTTOTRANSACTIONPROCESSING,
                ]
            )
            ->will($this->returnValue([$depositItem]));

        $emMock = $this->getEntityManagerMock();
        $emMock
            ->expects($this->exactly(0)) // check that DELETED item is not persisted
            ->method('persist');
        $emMock
            ->expects($this->exactly(0)) // check that DELETED item is not flushed
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock()
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(0, $result, 'Count of loaded checks should be 0');
    }

    /**
     * @test
     */
    public function shouldNotCreateOrderIfTransactionAlreadyExists()
    {
        $date = new \DateTime();
        $group = new Group();
        $batchNumber = 111;

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch->setBatchNumber($batchNumber);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                ]
            )
            ->will($this->returnValue([$remoteBatch]));
        $depositItem = new WSRemoteDepositItem();
        $depositItem
            ->setDeleted(false)
            ->setReferenceNumber('ref-test')
            ->setBatchNumber('b1');

        $rdcClientMock
            ->expects($this->once())
            ->method('getBatchItems')
            ->with(
                $group,
                $batchNumber,
                [
                    WSItemStatus::CREATED,
                    WSItemStatus::APPROVED,
                    WSItemStatus::SENTTOTRANSACTIONPROCESSING,
                ]
            )
            ->will($this->returnValue([$depositItem]));

        $emMock = $this->getEntityManagerMock();
        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(
                [
                    'transactionId' => 'ref-test',
                    'batchId' => 'b1',
                    'status' => 'complete'
                ]
            )
            ->will($this->returnValue(new Transaction()));

        $emMock
            ->expects($this->once())
            ->method('getRepository')
            ->with('RjDataBundle:Transaction')
            ->will($this->returnValue($repositoryMock));

        $emMock
            ->expects($this->exactly(0)) // check that item with already existing transaction is not persisted
            ->method('persist');
        $emMock
            ->expects($this->exactly(0)) // check that item with already existing transaction is not flushed
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock()
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(0, $result, 'Count of loaded checks should be 0');
    }

    /**
     * @test
     */
    public function shouldCreateOrderIfTransactionIsNew()
    {
        $date = new \DateTime();
        $group = new Group();
        $batchNumber = 111;

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch->setBatchNumber($batchNumber);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                ]
            )
            ->will($this->returnValue([$remoteBatch]));
        $depositItem1 = new WSRemoteDepositItem();
        $depositItem1
            ->setDeleted(false)
            ->setReferenceNumber('ref-test1')
            ->setBatchNumber('b1');

        $depositItem2 = new WSRemoteDepositItem();
        $depositItem2
            ->setDeleted(false)
            ->setReferenceNumber('ref-test2')
            ->setBatchNumber('b2');

        $rdcClientMock
            ->expects($this->once())
            ->method('getBatchItems')
            ->with(
                $group,
                $batchNumber,
                [
                    WSItemStatus::CREATED,
                    WSItemStatus::APPROVED,
                    WSItemStatus::SENTTOTRANSACTIONPROCESSING,
                ]
            )
            ->will($this->returnValue([$depositItem1, $depositItem2]));

        $order1 = new Order();
        $order1->setStatus(OrderStatus::PENDING);
        $order2 = new Order();
        $order2->setStatus(OrderStatus::COMPLETE);

        $emMock = $this->getEntityManagerMock();
        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->withConsecutive(
                [[
                    'transactionId' => 'ref-test1',
                    'batchId' => 'b1',
                    'status' => 'complete'
                ]],
                [[
                    'transactionId' => 'ref-test2',
                    'batchId' => 'b2',
                    'status' => 'complete'
                ]]
            )
            ->will($this->returnValue(null));

        $checkTransformerMock = $this->getBaseMock(ScannedCheckTransformer::class);
        $checkTransformerMock
            ->expects($this->exactly(2))
            ->method('transformToOrder')
            ->withConsecutive([$depositItem1], [$depositItem2])
            ->willReturnOnConsecutiveCalls([$order1], [$order2]);

        $emMock
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->with('RjDataBundle:Transaction')
            ->will($this->returnValue($repositoryMock));

        $emMock
            ->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive($order1, $order2);

        $emMock
            ->expects($this->exactly(2))
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $checkTransformerMock,
            $emMock,
            $this->getLoggerMock()
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(2, $result, 'Count of loaded checks should be 2');
    }
}
