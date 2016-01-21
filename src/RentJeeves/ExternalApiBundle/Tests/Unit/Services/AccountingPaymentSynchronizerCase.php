<?php
namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services;

use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\TestBundle\Mocks\CommonSystemMocks;

class AccountingPaymentSynchronizerCase extends \PHPUnit_Framework_TestCase
{
    /** @var AccountingPaymentSynchronizer $synchronizer */
    protected $synchronizer;

    protected function setUp()
    {
        $systemsMocks = new CommonSystemMocks();

        $this->synchronizer = new AccountingPaymentSynchronizer(
            $systemsMocks->getEntityManagerMock(),
            $this->getExternalApiClientFactoryMock(),
            $this->getSoapClientFactoryMock(),
            $systemsMocks->getSerializerMock(),
            $systemsMocks->getExceptionCatcherMock(),
            $systemsMocks->getLoggerMock()
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
                'getId'
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
