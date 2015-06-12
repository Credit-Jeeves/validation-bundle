<?php
namespace RentJeeves\ExternalApiBundle\Tests\Unit\Services;

use RentJeeves\ExternalApiBundle\Services\AccountingPaymentSynchronizer;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
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
        $supportedType = ApiIntegrationType::AMSI;
        $isIntegrated = true;
        $contractMock = $this->getContractMock($supportedType, $isIntegrated);

        $this->assertTrue(
            $this->synchronizer->isAllowedToSend($contractMock),
            "should send if supported type and integrated"
        );
    }

    /**
     * @test
     */
    public function shouldNotAllowToSendIfNotIntegrated()
    {
        $supportedType = ApiIntegrationType::AMSI;
        $isIntegrated = false;
        $contractMock = $this->getContractMock($supportedType, $isIntegrated);

        $this->assertFalse(
            $this->synchronizer->isAllowedToSend($contractMock),
            "should NOT send if supported type and NOT integrated"
        );
    }

    /**
     * @test
     */
    public function shouldNotAllowToSendIfUnsupportedType()
    {
        $supportedType = ApiIntegrationType::NONE;
        $isIntegrated = true;
        $contractMock = $this->getContractMock($supportedType, $isIntegrated);

        $this->assertFalse(
            $this->synchronizer->isAllowedToSend($contractMock),
            "should NOT send if NOT supported type"
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\RentJeeves\DataBundle\Entity\Contract
     */
    protected function getContractMock($integrationType, $isIntegrated)
    {
        $mock = $this->getMock('\RentJeeves\DataBundle\Entity\Contract', [], [], '', false);

        // add a holding that will return integration type
        $holdingMock = $this->getMock(
            '\RentJeeves\DataBundle\Entity\Holding',
            ['getApiIntegrationType'],
            [],
            '',
            false
        );
        $holdingMock->method('getApiIntegrationType')
            ->will($this->returnValue($integrationType));
        $mock->method('getHolding')
            ->will($this->returnValue($holdingMock));

        // add a group and it's settings to return isIntegrated
        $settingMock = $this->getMock('\RentJeeves\DataBundle\Entity\GroupSettings', [], [], '', false);
        $settingMock->method('getIsIntegrated')
            ->will($this->returnValue($isIntegrated));
        $groupMock = $this->getMock('\RentJeeves\DataBundle\Entity\Group', ['getGroupSettings'], [], '', false);
        $groupMock->method('getGroupSettings')
            ->will($this->returnValue($settingMock));
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
