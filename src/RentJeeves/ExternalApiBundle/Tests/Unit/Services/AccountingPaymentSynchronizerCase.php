<?php
namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\PaymentBatchMapping;
use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class AccountingPaymentSynchronizerCase extends \PHPUnit_Framework_TestCase
{
    use CreateSystemMocksExtensionTrait;

    /** @var AccountingPaymentSynchronizer $synchronizer */
    protected $synchronizer;

    protected function setUp()
    {
        $this->synchronizer = new AccountingPaymentSynchronizer(
            $this->getEntityManagerMock(),
            $this->getExternalApiClientFactoryMock(),
            $this->getSoapClientFactoryMock(),
            $this->getSerializerMock(),
            $this->getExceptionCatcherMock(),
            $this->getLoggerMock()
        );
    }

    /**
     * @test
     */
    public function shouldAllowToSendIfIntegrated()
    {
        $apiType = AccountingSystem::AMSI;
        $isIntegrated = true;
        $orderMock = $this->getOrderMock($apiType, $isIntegrated);

        $this->assertTrue(
            $this->synchronizer->isAllowedToSend($orderMock),
            "should send if supported type and integrated"
        );
    }

    /**
     * @test
     */
    public function shouldNotAllowToSendIfNotIntegrated()
    {
        $apiType = AccountingSystem::AMSI;
        $isIntegrated = false;
        $orderMock = $this->getOrderMock($apiType, $isIntegrated);

        $this->assertFalse(
            $this->synchronizer->isAllowedToSend($orderMock),
            "should NOT send if supported type and NOT integrated"
        );
    }

    /**
     * @test
     */
    public function shouldNotAllowToSendIfUnsupportedType()
    {
        $apiType = AccountingSystem::NONE;
        $isIntegrated = true;
        $orderMock = $this->getOrderMock($apiType, $isIntegrated);

        $this->assertFalse(
            $this->synchronizer->isAllowedToSend($orderMock),
            "should NOT send if NOT supported type"
        );
    }

    /**
     * @test
     */
    public function shouldNotAllowToSendIfNotAllowedToSendRealTime()
    {
        $apiType = AccountingSystem::YARDI_VOYAGER;
        $isIntegrated = true;
        $isAllowedRealTime = false;
        $orderMock = $this->getOrderMock($apiType, $isIntegrated, $isAllowedRealTime);

        $this->assertFalse(
            $this->synchronizer->isAllowedToSend($orderMock),
            "should NOT send if holding is not allowed to send realtime"
        );
    }

    /**
     * @test
     */
    public function shouldNotAllowToSendCustomOperationIfPostAppFeeSecurityDepositInHoldingSwitchedOff()
    {
        $apiType = AccountingSystem::RESMAN;
        $isIntegrated = true;
        $isAllowedRealTime = true;
        $hasCustomOperation = true;
        $postAppFee = false;

        $orderMock = $this->getOrderMock($apiType, $isIntegrated, $isAllowedRealTime, $hasCustomOperation, $postAppFee);

        $this->assertFalse(
            $this->synchronizer->isAllowedToSend($orderMock),
            'Order should NOT be allowed to send custom operation if Post App Fee for holding switched off'
        );
    }

    /**
     * @test
     */
    public function shouldNotAllowToSendCustomOperationIfPostAppFeeSecurityDepositInHoldingSwitchedOnButASNotResMan()
    {
        $apiType = AccountingSystem::YARDI_VOYAGER;
        $isIntegrated = true;
        $isAllowedRealTime = true;
        $hasCustomOperation = true;
        $postAppFee = true;

        $orderMock = $this->getOrderMock($apiType, $isIntegrated, $isAllowedRealTime, $hasCustomOperation, $postAppFee);

        $this->assertFalse(
            $this->synchronizer->isAllowedToSend($orderMock),
            'Order should NOT be allowed to send custom operation if Accounting System is not ResMan'
        );
    }

    /**
     * @test
     */
    public function shouldAllowToSendCustomOperationIfPostAppFeeSecurityDepositInHoldingSwitchedOnAndASIsResMan()
    {
        $apiType = AccountingSystem::RESMAN;
        $isIntegrated = true;
        $isAllowedRealTime = true;
        $hasCustomOperation = true;
        $postAppFee = true;

        $orderMock = $this->getOrderMock($apiType, $isIntegrated, $isAllowedRealTime, $hasCustomOperation, $postAppFee);

        $this->assertTrue(
            $this->synchronizer->isAllowedToSend($orderMock),
            'Order should be allowed to send custom operation if Post App Fee for holding switched on & AS = ResMan'
        );
    }

    /**
     * @return array
     */
    public function dataProviderForCloseBatchFailure()
    {
        return [
            [
                AccountingSystem::YARDI_VOYAGER,
                'RentJeeves\DataBundle\Entity\YardiSettings',
                'RentJeeves\ExternalApiBundle\Services\Yardi\Clients\PaymentClient',
                'setYardiSettings',
                ',555,--app=rj'
            ],
            [
                AccountingSystem::RESMAN,
                'RentJeeves\DataBundle\Entity\ResManSettings',
                'RentJeeves\ExternalApiBundle\Services\ResMan\ResManClient',
                'setResManSettings',
                ',--app=rj'
            ]
        ];
    }

    /**
     * @param string $accountingSystem
     * @param string $externalSettingsClassName
     * @param string $paymentClient
     * @param string $setterSettings
     * @param string $argumentsInCommand
     *
     * @test
     * @dataProvider dataProviderForCloseBatchFailure
     */
    public function closeBatchFailureShouldCreateJobNotify(
        $accountingSystem,
        $externalSettingsClassName,
        $paymentClient,
        $setterSettings,
        $argumentsInCommand
    ) {
        $em = $this->getEntityManagerMock();
        $paymentBatchMappingRep = $this->getBaseMock('RentJeeves\DataBundle\Entity\PaymentBatchMappingRepository');
        $transactionRepository = $this->getBaseMock('RentJeeves\DataBundle\Entity\TransactionRepository');
        $paymentClient = $this->getBaseMock($paymentClient);

        $paymentClient->expects($this->at(0))
            ->method('setDebug');

        $paymentClient->expects($this->at(1))
            ->method('closeBatch')
            ->with('555', '777')
            ->will($this->returnValue(false));

        $holding = new Holding();
        $holding->setAccountingSystem($accountingSystem);
        $holding->$setterSettings(new $externalSettingsClassName());

        $clientFactory = $this->getExternalApiClientFactoryMock();
        $clientFactory->expects($this->at(0))
            ->method('createClient')
            ->will($this->returnValue($paymentClient));

        $transactionRepository->expects($this->at(0))
            ->method('getMerchantHoldingByBatchId')
            ->with('666')
            ->will($this->returnValue($holding));

        $paymentBatchMapping = new PaymentBatchMapping();
        $paymentBatchMapping->setPaymentBatchId('666');
        $paymentBatchMapping->setAccountingBatchId('555');
        $paymentBatchMapping->setExternalPropertyId('777');
        $paymentBatchMapping->setAccountingPackageType($accountingSystem);
        $paymentBatchMapping->setOpenedAt(new \DateTime());

        $paymentBatchMappingRep->expects($this->once())
            ->method('getTodayBatches')
            ->with($this->equalTo($accountingSystem))
            ->will($this->returnValue([$paymentBatchMapping]));

        $em->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:PaymentBatchMapping'))
            ->will($this->returnValue($paymentBatchMappingRep));

        $em->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo('RjDataBundle:Transaction'))
            ->will($this->returnValue($transactionRepository));

        $em->expects($this->at(2))
            ->method('persist')
            ->willReturnCallback(function ($job) use ($argumentsInCommand) {
                $this->assertInstanceOf('RentJeeves\DataBundle\Entity\Job', $job, 'Job should create');
                $this->assertEquals(
                    $job->getCommand(),
                    'renttrack:notify:batch-close-failure',
                    'Command should be failure'
                );
                $this->assertEquals(
                    implode(',', $job->getArgs()),
                    $argumentsInCommand,
                    'Command should have arguments'
                );

                return null;
            });

        $em->expects($this->at(3))
            ->method('flush');

        $logger = $this->getLoggerMock();
        $logger->expects($this->at(0))
            ->method('debug')
            ->with('Batch ID: failed to close');

        $synchronizer = new AccountingPaymentSynchronizer(
            $em,
            $clientFactory,
            $this->getSoapClientFactoryMock(),
            $this->getSerializerMock(),
            $this->getExceptionCatcherMock(),
            $logger
        );

        $synchronizer->closeBatches($accountingSystem);
    }

    /**
     * @param string $integrationType
     * @param bool $isIntegrated
     * @param bool $isAllowedRealTime
     * @param bool $hasCustomOperation
     * @return \CreditJeeves\DataBundle\Entity\Order
     */
    protected function getOrderMock(
        $integrationType,
        $isIntegrated,
        $isAllowedRealTime = true,
        $hasCustomOperation = false,
        $postNotRent = false
    ) {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\CreditJeeves\DataBundle\Entity\Order $mock */
        $mock = $this->getMock('\CreditJeeves\DataBundle\Entity\Order', [], [], '', false);

        $mock->method('getCustomOperation')
            ->will(
                $this->returnValue(
                    $hasCustomOperation
                )
            );

        $mock->method('getContract')
            ->will(
                $this->returnValue(
                    $this->getContractMock($integrationType, $isIntegrated, $isAllowedRealTime, $postNotRent)
                )
            );

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\DataBundle\Entity\Contract
     */
    protected function getContractMock($integrationType, $isIntegrated, $isAllowedRealTime = true, $postNotRent = false)
    {
        $mock = $this->getMock('\RentJeeves\DataBundle\Entity\Contract', [], [], '', false);

        // add a holding that will return integration type
        $holdingMock = $this->getMock(
            '\RentJeeves\DataBundle\Entity\Holding',
            [
                'getAccountingSystem',
                'isAllowedToSendRealTimePayments',
                'isPostAppFeeAndSecurityDeposit',
                'getName',
                'getId',
                'isApiIntegrated'
            ],
            [],
            '',
            false
        );
        $holdingMock->method('getAccountingSystem')
            ->will($this->returnValue($integrationType));
        $holdingMock->method('isAllowedToSendRealTimePayments')
            ->will($this->returnValue($isAllowedRealTime));
        $holdingMock->method('isPostAppFeeAndSecurityDeposit')
            ->will($this->returnValue($postNotRent));
        $holdingMock->method('isApiIntegrated')
            ->will($this->returnValue(true));
        $mock->method('getHolding')
            ->will($this->returnValue($holdingMock));

        // add a group and it's settings to return isIntegrated
        $settingMock = $this->getMock('\RentJeeves\DataBundle\Entity\GroupSettings', [], [], '', false);
        $settingMock->method('getIsIntegrated')
            ->will($this->returnValue($isIntegrated));

        $groupMock = $this->getMock(
            '\RentJeeves\DataBundle\Entity\Group',
            ['getGroupSettings', 'isExistGroupSettings'],
            [],
            '',
            false
        );
        $groupMock->method('getGroupSettings')
            ->will($this->returnValue($settingMock));
        $groupMock->method('isExistGroupSettings')
            ->will($this->returnValue(true));
        $mock->method('getGroup')
            ->will($this->returnValue($groupMock));

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\ExternalApiBundle\Soap\SoapClientFactory
     */
    protected function getSoapClientFactoryMock()
    {
        return $this->getMock('\RentJeeves\ExternalApiBundle\Soap\SoapClientFactory', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\ExternalApiBundle\Services\ExternalApiClientFactory
     */
    protected function getExternalApiClientFactoryMock()
    {
        return $this->getMock('\RentJeeves\ExternalApiBundle\Services\ExternalApiClientFactory', [], [], '', false);
    }
}
