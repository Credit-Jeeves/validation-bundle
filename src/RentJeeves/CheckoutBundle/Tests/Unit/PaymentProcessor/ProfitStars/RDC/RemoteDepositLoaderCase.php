<?php

namespace RentJeeves\CheckoutBundle\Tests\Unit\PaymentProcessor\ProfitStars\RDC;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Operation;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderPaymentType;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\RDCClient;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\RemoteDepositLoader;
use RentJeeves\CheckoutBundle\PaymentProcessor\ProfitStars\RDC\ScannedCheckTransformer;
use RentJeeves\CoreBundle\ContractManagement\ContractManager;
use RentJeeves\DataBundle\Entity\AMSISettings;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\ProfitStarsBatch;
use RentJeeves\DataBundle\Entity\Transaction;
use RentJeeves\DataBundle\Entity\TransactionRepository;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\DataBundle\Enum\TransactionStatus;
use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;
use RentJeeves\ExternalApiBundle\Services\EmailNotifier\FailedPostPaymentNotifier;
use RentJeeves\ExternalApiBundle\Services\ExternalApiClientFactory;
use RentJeeves\ExternalApiBundle\Soap\SoapClientFactory;
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
            $this->getBaseMock(ContractManager::class),
            $this->getBaseMock(AccountingPaymentSynchronizer::class)
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
            ->setItemStatus(WSItemStatus::SENTTOTRANSACTIONPROCESSING)
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

        $order = new Order();
        $order->setStatus(OrderStatus::PENDING);
        $transaction = new Transaction();
        $transaction->setTransactionId('ref-test');
        $transaction->setOrder($order);
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
            ->expects($this->exactly(3))
            ->method('flush');

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class),
            $this->getBaseMock(AccountingPaymentSynchronizer::class)
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(0, $result, 'Count of loaded checks should be 0');
        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus(), 'Order status should be COMPLETE');
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
            $this->getBaseMock(ContractManager::class),
            $this->getBaseMock(AccountingPaymentSynchronizer::class)
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
            $this->getBaseMock(ContractManager::class),
            $this->getBaseMock(AccountingPaymentSynchronizer::class)
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
            $this->getBaseMock(ContractManager::class),
            $this->getBaseMock(AccountingPaymentSynchronizer::class)
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
            $contractManagerMock,
            $this->getBaseMock(AccountingPaymentSynchronizer::class)
        );

        $result = $loader->loadScannedChecks($group, $date);

        $this->assertEquals(1, $result, 'Count of loaded checks should be 1');
    }

    /**
     * @test
     */
    public function shouldUpdateTransactionIdAndCreateJobToPushToASWhenTransactionIdWasEmpty()
    {
        $date = new \DateTime();
        $group = new Group();
        $groupSetting = new GroupSettings();
        $groupSetting->setIsIntegrated(true);
        $group->setGroupSettings($groupSetting);
        $batchNumber = 111;
        $holding = new Holding();
        $holding->setAccountingSystem(AccountingSystem::AMSI);
        $holding->setIsAllowedFutureContract(true);

        $amsiSetting = new AMSISettings();
        $holding->setAmsiSettings($amsiSetting);
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
            ->setItemStatus(WSItemStatus::SENTTOTRANSACTIONPROCESSING)
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

        $order = new Order();
        $order->setPaymentType(OrderPaymentType::SCANNED_CHECK);
        $order->setStatus(OrderStatus::PENDING);
        $order->setSum(123);
        $order->setFee(0);
        $order->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);

        $contract = new Contract();
        $contract->setStatus(ContractStatus::WAITING);
        $contract->setHolding($holding);
        $contract->setGroup($group);

        $operation = new Operation();
        $operation->setContract($contract);
        $operation->setOrder($order);
        $order->addOperation($operation);
        $operation->setContract($contract);

        $transaction = new Transaction();
        $transaction->setStatus(TransactionStatus::COMPLETE);
        $transaction->setIsSuccessful(true);
        $transaction->setBatchId(12345);
        $transaction->setAmount(123);
        $transaction->setOrder($order);

        $order->addTransaction($transaction);
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
            ->expects($this->exactly(2))
            ->method('persist')
            ->withConsecutive(
                $this->isInstanceOf(ProfitStarsBatch::class),
                $this->isInstanceOf(Job::class)
            );

        $emMock
            ->expects($this->exactly(4))
            ->method('flush');

        /** @var AccountingPaymentSynchronizer $synchronizer */
        $synchronizer = new AccountingPaymentSynchronizer(
            $emMock,
            $this->getBaseMock(ExternalApiClientFactory::class),
            $this->getBaseMock(SoapClientFactory::class),
            $this->getSerializerMock(),
            $this->getLoggerMock(),
            $this->getBaseMock(FailedPostPaymentNotifier::class)
        );

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class),
            $synchronizer
        );
        $loader->loadScannedChecks($group, $date);

        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus(), 'Order status should be COMPLETE');
        $this->assertEquals('ref-test', $transaction->getTransactionId(), 'TransactionId should be updated');
    }

    /**
     * @test
     */
    public function shouldUpdateAmountIfOrderSumDiffersWithItemAmountWhenMovingOrderToComplete()
    {
        $date = new \DateTime();
        $group = new Group();

        $batchNumber = 125874;
        $orderAmount = 129.55;
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
            ->setTotalAmount(120.55)
            ->setDeleted(false)
            ->setItemStatus(WSItemStatus::SENTTOTRANSACTIONPROCESSING)
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

        $order = new Order();
        $order->setPaymentType(OrderPaymentType::SCANNED_CHECK);
        $order->setStatus(OrderStatus::PENDING);
        $order->setSum($orderAmount);
        $order->setFee(0);
        $order->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);

        $contract = new Contract();
        $contract->setStatus(ContractStatus::WAITING);
        $contract->setHolding($holding);
        $contract->setGroup($group);

        $operation = new Operation();
        $operation->setAmount($orderAmount);
        $operation->setContract($contract);
        $operation->setOrder($order);
        $order->addOperation($operation);
        $operation->setContract($contract);

        $transaction = new Transaction();
        $transaction->setStatus(TransactionStatus::COMPLETE);
        $transaction->setIsSuccessful(true);
        $transaction->setBatchId(12345);
        $transaction->setAmount($orderAmount);
        $transaction->setOrder($order);

        $order->addTransaction($transaction);
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
            ->expects($this->exactly(1))
            ->method('persist')
            ->with($this->isInstanceOf(ProfitStarsBatch::class));

        $emMock
            ->expects($this->exactly(3))
            ->method('flush');

        /** @var AccountingPaymentSynchronizer $synchronizer */
        $synchronizer = new AccountingPaymentSynchronizer(
            $emMock,
            $this->getBaseMock(ExternalApiClientFactory::class),
            $this->getBaseMock(SoapClientFactory::class),
            $this->getSerializerMock(),
            $this->getLoggerMock(),
            $this->getBaseMock(FailedPostPaymentNotifier::class)
        );

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class),
            $synchronizer
        );
        $loader->loadScannedChecks($group, $date);

        $this->assertEquals(OrderStatus::COMPLETE, $order->getStatus(), 'Order status should be COMPLETE');
        $this->assertEquals(120.55, $transaction->getAmount(), 'Transaction amount should be updated');
        $this->assertEquals(120.55, $order->getSum(), 'Order SUM should be updated');
        $this->assertEquals(120.55, $order->getOperations()->first()->getAmount(), 'Operation SUM should be updated');
    }

    /**
     * @test
     */
    public function shouldMoveOrderToErrorWhenTransactionExistsAndDepositItemIsInErrorState()
    {
        $date = new \DateTime();
        $group = new Group();

        $batchNumber = 125874;
        $orderAmount = 100;
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
            ->setTotalAmount(120.55)
            ->setDeleted(false)
            ->setItemStatus(WSItemStatus::TPERROR)
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

        $order = new Order();
        $order->setPaymentType(OrderPaymentType::SCANNED_CHECK);
        $order->setStatus(OrderStatus::PENDING);
        $order->setSum($orderAmount);
        $order->setFee(0);
        $order->setPaymentProcessor(PaymentProcessor::PROFIT_STARS);

        $contract = new Contract();
        $contract->setStatus(ContractStatus::WAITING);
        $contract->setHolding($holding);
        $contract->setGroup($group);

        $operation = new Operation();
        $operation->setAmount($orderAmount);
        $operation->setContract($contract);
        $operation->setOrder($order);
        $order->addOperation($operation);
        $operation->setContract($contract);

        $transaction = new Transaction();
        $transaction->setStatus(TransactionStatus::COMPLETE);
        $transaction->setIsSuccessful(true);
        $transaction->setBatchId(12345);
        $transaction->setAmount($orderAmount);
        $transaction->setOrder($order);

        $order->addTransaction($transaction);
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
            ->expects($this->exactly(1))
            ->method('persist')
            ->with($this->isInstanceOf(ProfitStarsBatch::class));

        $emMock
            ->expects($this->exactly(3))
            ->method('flush');

        /** @var AccountingPaymentSynchronizer $synchronizer */
        $synchronizer = new AccountingPaymentSynchronizer(
            $emMock,
            $this->getBaseMock(ExternalApiClientFactory::class),
            $this->getBaseMock(SoapClientFactory::class),
            $this->getSerializerMock(),
            $this->getLoggerMock(),
            $this->getBaseMock(FailedPostPaymentNotifier::class)
        );

        $loader = new RemoteDepositLoader(
            $rdcClientMock,
            $this->getBaseMock(ScannedCheckTransformer::class),
            $emMock,
            $this->getLoggerMock(),
            $this->getBaseMock(ContractManager::class),
            $synchronizer
        );
        $loader->loadScannedChecks($group, $date);

        $this->assertEquals(OrderStatus::ERROR, $order->getStatus(), 'Order status should be COMPLETE');
        $this->assertEquals('Check Scanning Error: Refer to Check Scanning Interface.', $transaction->getMessages());
    }
}
