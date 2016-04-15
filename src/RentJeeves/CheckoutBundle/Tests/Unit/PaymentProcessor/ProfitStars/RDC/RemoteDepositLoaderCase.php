<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\RDCClient;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\RemoteDepositLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ScannedCheckTransformer;
use RentJeeves\CoreBundle\ContractManagement\ContractManager;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\ProfitStarsBatch;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\DataBundle\Enum\ContractStatus;
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

        $holding = new Holding();
        $group->setHolding($holding);

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch->setBatchNumber($batchNumber);
        $remoteBatch->setBatchStatus(WSBatchStatus::SENTTOTRANSACTIONPROCESSING);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::OPEN,
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::ERROR,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::REJECTED,
                    WSBatchStatus::DELETED,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                    WSBatchStatus::TPERROR,
                    WSBatchStatus::NEEDSBALANCING,
                    WSBatchStatus::PARTIALLYPROCESSED,
                    WSBatchStatus::TPBATCHCREATIONFAILED,
                    WSBatchStatus::PARTIALDEPOSIT
                ]
            )
            ->will($this->returnValue([$remoteBatch]));

        $depositItem = new WSRemoteDepositItem();
        $depositItem->setDeleted(true);
        $depositItem->setItemStatus(WSItemStatus::APPROVED);

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
                    WSItemStatus::CLOSED,
                    WSItemStatus::DELETED,
                    WSItemStatus::ERROR,
                    WSItemStatus::CHECKDECISIONINGERROR,
                    WSItemStatus::NEEDSATTENTION,
                    WSItemStatus::NEEDSRESCAN,
                    WSItemStatus::REJECTED,
                    WSItemStatus::RELEASED,
                    WSItemStatus::RESCANNED,
                    WSItemStatus::TPERROR,
                    WSItemStatus::RESOLVED,
                    WSItemStatus::NONE
                ]
            )
            ->will($this->returnValue([$depositItem]));

        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['batchNumber' => $batchNumber])
            ->will($this->returnValue(null));

        $emMock = $this->getEntityManagerMock();
        $emMock
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ProfitStarsBatch'))
            ->will($this->returnValue($repositoryMock));

        $emMock
            ->expects($this->once()) // when create new ProfitStarsBatch
            ->method('persist')
            ->with($this->isInstanceOf(ProfitStarsBatch::class));
        $emMock
            ->expects($this->exactly(2))
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class)
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

        $holding = new Holding();
        $group->setHolding($holding);

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch
            ->setBatchNumber($batchNumber)
            ->setBatchStatus(WSBatchStatus::SENTTOTRANSACTIONPROCESSING);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::OPEN,
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::ERROR,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::REJECTED,
                    WSBatchStatus::DELETED,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                    WSBatchStatus::TPERROR,
                    WSBatchStatus::NEEDSBALANCING,
                    WSBatchStatus::PARTIALLYPROCESSED,
                    WSBatchStatus::TPBATCHCREATIONFAILED,
                    WSBatchStatus::PARTIALDEPOSIT
                ]
            )
            ->will($this->returnValue([$remoteBatch]));
        $depositItem = new WSRemoteDepositItem();
        $depositItem
            ->setItemId(123)
            ->setDeleted(false)
            ->setItemStatus(WSItemStatus::APPROVED)
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
                    WSItemStatus::CLOSED,
                    WSItemStatus::DELETED,
                    WSItemStatus::ERROR,
                    WSItemStatus::CHECKDECISIONINGERROR,
                    WSItemStatus::NEEDSATTENTION,
                    WSItemStatus::NEEDSRESCAN,
                    WSItemStatus::REJECTED,
                    WSItemStatus::RELEASED,
                    WSItemStatus::RESCANNED,
                    WSItemStatus::TPERROR,
                    WSItemStatus::RESOLVED,
                    WSItemStatus::NONE
                ]
            )
            ->will($this->returnValue([$depositItem]));

        $emMock = $this->getEntityManagerMock();
        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['batchNumber' => $batchNumber])
            ->will($this->returnValue(null));

        $transaction = new Transaction();
        $transaction->setTransactionId('ref-test');
        $transactionRepositoryMock = $this->getBaseMock(TransactionRepository::class);
        $transactionRepositoryMock
            ->expects($this->once())
            ->method('getTransactionByProfitStarsItemId')
            ->with($this->equalTo(123))
            ->will($this->returnValue($transaction));

        $emMock
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(['RjDataBundle:ProfitStarsBatch'], ['RjDataBundle:Transaction'])
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($repositoryMock),
                    $this->returnValue($transactionRepositoryMock)
                )
            );

        $emMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(ProfitStarsBatch::class));
        $emMock
            ->expects($this->exactly(2))
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class)
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(0, $result, 'Count of loaded checks should be 0');
    }

    /**
     * @test
     */
    public function shouldCreateNewBatchWithOrdersIfItemIsNew()
    {
        $date = new \DateTime();
        $group = new Group();
        $batchNumber = 111;

        $holding = new Holding();
        $group->setHolding($holding);

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch
            ->setBatchNumber($batchNumber)
            ->setBatchStatus(WSBatchStatus::SENTTOTRANSACTIONPROCESSING);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::OPEN,
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::ERROR,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::REJECTED,
                    WSBatchStatus::DELETED,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                    WSBatchStatus::TPERROR,
                    WSBatchStatus::NEEDSBALANCING,
                    WSBatchStatus::PARTIALLYPROCESSED,
                    WSBatchStatus::TPBATCHCREATIONFAILED,
                    WSBatchStatus::PARTIALDEPOSIT
                ]
            )
            ->will($this->returnValue([$remoteBatch]));
        $depositItem1 = new WSRemoteDepositItem();
        $depositItem1
            ->setItemId(123)
            ->setDeleted(false)
            ->setItemStatus(WSItemStatus::APPROVED)
            ->setReferenceNumber('ref-test1')
            ->setBatchNumber('b1');

        $depositItem2 = new WSRemoteDepositItem();
        $depositItem2
            ->setItemId(124)
            ->setDeleted(false)
            ->setItemStatus(WSItemStatus::SENTTOTRANSACTIONPROCESSING)
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
                    WSItemStatus::CLOSED,
                    WSItemStatus::DELETED,
                    WSItemStatus::ERROR,
                    WSItemStatus::CHECKDECISIONINGERROR,
                    WSItemStatus::NEEDSATTENTION,
                    WSItemStatus::NEEDSRESCAN,
                    WSItemStatus::REJECTED,
                    WSItemStatus::RELEASED,
                    WSItemStatus::RESCANNED,
                    WSItemStatus::TPERROR,
                    WSItemStatus::RESOLVED,
                    WSItemStatus::NONE
                ]
            )
            ->will($this->returnValue([$depositItem1, $depositItem2]));

        $profitStarsBatch = new ProfitStarsBatch();
        $order1 = new Order();
        $order1->setStatus(OrderStatus::PENDING);
        $order2 = new Order();
        $order2->setStatus(OrderStatus::COMPLETE);

        $emMock = $this->getEntityManagerMock();
        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('findOneBy')
            ->with(['batchNumber' => $batchNumber])
            ->will($this->returnValue(null));

        $checkTransformerMock = $this->getBaseMock(ScannedCheckTransformer::class);
        $checkTransformerMock
            ->expects($this->exactly(2))
            ->method('transformToOrder')
            ->withConsecutive([$depositItem1], [$depositItem2])
            ->willReturnOnConsecutiveCalls($order1, $order2);

        $emMock
            ->expects($this->exactly(3))
            ->method('getRepository')
            ->withConsecutive(
                ['RjDataBundle:ProfitStarsBatch'],
                ['RjDataBundle:Transaction'],
                ['RjDataBundle:Transaction']
            )
            ->will($this->returnValue($repositoryMock));

        $emMock
            ->expects($this->exactly(3))
            ->method('persist')
            ->withConsecutive($profitStarsBatch, $order1, $order2);

        $emMock
            ->expects($this->exactly(4))
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $checkTransformerMock,
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class)
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(2, $result, 'Count of loaded checks should be 2');
    }

    /**
     * @test
     */
    public function shouldNotCreateNeitherBatchNorOrderWhenBatchStatusIsNotAllowed()
    {
        $date = new \DateTime();
        $group = new Group();
        $batchNumber = 111;

        $holding = new Holding();
        $group->setHolding($holding);

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch->setBatchNumber($batchNumber);
        $remoteBatch->setBatchStatus(WSBatchStatus::ERROR);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::OPEN,
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::ERROR,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::REJECTED,
                    WSBatchStatus::DELETED,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                    WSBatchStatus::TPERROR,
                    WSBatchStatus::NEEDSBALANCING,
                    WSBatchStatus::PARTIALLYPROCESSED,
                    WSBatchStatus::TPBATCHCREATIONFAILED,
                    WSBatchStatus::PARTIALDEPOSIT
                ]
            )
            ->will($this->returnValue([$remoteBatch]));

        $depositItem = new WSRemoteDepositItem();
        $depositItem->setDeleted(true);
        $depositItem->setItemStatus(WSItemStatus::APPROVED);

        $rdcClientMock
            ->expects($this->never())
            ->method('getBatchItems');

        $emMock = $this->getEntityManagerMock();

        $emMock
            ->expects($this->never())
            ->method('persist');
        $emMock
            ->expects($this->never())
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class)
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(0, $result, 'Count of loaded checks should be 0');
    }

    /**
     * @test
     */
    public function shouldNotCreateOrderWhenBatchStatusIsAllowedButItemStatusNotAllowed()
    {
        $date = new \DateTime();
        $group = new Group();
        $batchNumber = 111;

        $holding = new Holding();
        $group->setHolding($holding);

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch->setBatchNumber($batchNumber);
        $remoteBatch->setBatchStatus(WSBatchStatus::SENTTOTRANSACTIONPROCESSING);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::OPEN,
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::ERROR,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::REJECTED,
                    WSBatchStatus::DELETED,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                    WSBatchStatus::TPERROR,
                    WSBatchStatus::NEEDSBALANCING,
                    WSBatchStatus::PARTIALLYPROCESSED,
                    WSBatchStatus::TPBATCHCREATIONFAILED,
                    WSBatchStatus::PARTIALDEPOSIT
                ]
            )
            ->will($this->returnValue([$remoteBatch]));

        $depositItem = new WSRemoteDepositItem();
        $depositItem->setDeleted(true);
        $depositItem->setItemStatus(WSItemStatus::CHECKDECISIONINGERROR); // status not allowed

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
                    WSItemStatus::CLOSED,
                    WSItemStatus::DELETED,
                    WSItemStatus::ERROR,
                    WSItemStatus::CHECKDECISIONINGERROR,
                    WSItemStatus::NEEDSATTENTION,
                    WSItemStatus::NEEDSRESCAN,
                    WSItemStatus::REJECTED,
                    WSItemStatus::RELEASED,
                    WSItemStatus::RESCANNED,
                    WSItemStatus::TPERROR,
                    WSItemStatus::RESOLVED,
                    WSItemStatus::NONE
                ]
            )
            ->will($this->returnValue([$depositItem]));

        $repositoryMock = $this->getEntityRepositoryMock();
        $repositoryMock
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['batchNumber' => $batchNumber])
            ->will($this->returnValue(null));

        $emMock = $this->getEntityManagerMock();
        $emMock
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:ProfitStarsBatch'))
            ->will($this->returnValue($repositoryMock));

        $emMock
            ->expects($this->once()) // when create new ProfitStarsBatch
            ->method('persist')
            ->with($this->isInstanceOf(ProfitStarsBatch::class));

        $emMock
            ->expects($this->exactly(2)) // when creating and then updating ProfitStarsBatch
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class)
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(0, $result, 'Count of loaded checks should be 0');
    }

    /**
     * @test
     */
    public function shouldCallMoveContractOutOfWaitingWhenNewOrderIsForContractInWaitingState()
    {
        $date = new \DateTime();
        $group = new Group();
        $batchNumber = 111;

        $holding = new Holding();
        $group->setHolding($holding);

        $remoteBatch = new WSRemoteDepositBatch();
        $remoteBatch
            ->setBatchNumber($batchNumber)
            ->setBatchStatus(WSBatchStatus::SENTTOTRANSACTIONPROCESSING);

        $rdcClientMock = $this->getBaseMock(RDCClient::class);
        $rdcClientMock
            ->expects($this->once())
            ->method('getBatches')
            ->with(
                $group,
                $date,
                [
                    WSBatchStatus::OPEN,
                    WSBatchStatus::CLOSED,
                    WSBatchStatus::ERROR,
                    WSBatchStatus::READYFORPROCESSING,
                    WSBatchStatus::REJECTED,
                    WSBatchStatus::DELETED,
                    WSBatchStatus::SENTTOTRANSACTIONPROCESSING,
                    WSBatchStatus::TPERROR,
                    WSBatchStatus::NEEDSBALANCING,
                    WSBatchStatus::PARTIALLYPROCESSED,
                    WSBatchStatus::TPBATCHCREATIONFAILED,
                    WSBatchStatus::PARTIALDEPOSIT
                ]
            )
            ->will($this->returnValue([$remoteBatch]));
        $depositItem1 = new WSRemoteDepositItem();
        $depositItem1
            ->setItemId(123)
            ->setDeleted(false)
            ->setItemStatus(WSItemStatus::APPROVED)
            ->setReferenceNumber('ref-test1')
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
                    WSItemStatus::CLOSED,
                    WSItemStatus::DELETED,
                    WSItemStatus::ERROR,
                    WSItemStatus::CHECKDECISIONINGERROR,
                    WSItemStatus::NEEDSATTENTION,
                    WSItemStatus::NEEDSRESCAN,
                    WSItemStatus::REJECTED,
                    WSItemStatus::RELEASED,
                    WSItemStatus::RESCANNED,
                    WSItemStatus::TPERROR,
                    WSItemStatus::RESOLVED,
                    WSItemStatus::NONE
                ]
            )
            ->will($this->returnValue([$depositItem1]));

        $profitStarsBatch = new ProfitStarsBatch();
        $order1 = new Order();
        $order1->setStatus(OrderStatus::COMPLETE);
        $contract = new Contract();
        $contract->setStatus(ContractStatus::WAITING);
        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setOrder($order1);
        $order1->addOperation($operation);

        $emMock = $this->getEntityManagerMock();
        $repositoryMock = $this->getBaseMock(TransactionRepository::class);
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('getTransactionByProfitStarsItemId')
            ->with(123)
            ->will($this->returnValue(null));

        $checkTransformerMock = $this->getBaseMock(ScannedCheckTransformer::class);
        $checkTransformerMock
            ->expects($this->exactly(1))
            ->method('transformToOrder')
            ->with($depositItem1)
            ->will($this->returnValue($order1));

        $emMock
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['RjDataBundle:ProfitStarsBatch'],
                ['RjDataBundle:Transaction']
            )
            ->will($this->returnValue($repositoryMock));

        $emMock
            ->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive($profitStarsBatch, $order1);

        $emMock
            ->expects($this->exactly(3))
            ->method('flush');

        $contractManagerMock = $this->getBaseMock(ContractManager::class);
        $contractManagerMock
            ->expects($this->once())
            ->method('moveContractOutOfWaitingByLandlord')
            ->with($contract, ContractStatus::CURRENT);

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $checkTransformerMock,
            $emMock,
            $this->getLoggerMock(),
            $contractManagerMock
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(1, $result, 'Count of loaded checks should be 1');
    }
}
