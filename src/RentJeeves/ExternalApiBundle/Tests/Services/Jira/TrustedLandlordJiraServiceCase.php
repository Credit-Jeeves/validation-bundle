<?php

namespace RentJeeves\ExternalApiBundle\Tests\Services\Jira;

use RentJeeves\DataBundle\Entity\CheckMailingAddress;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping;
use RentJeeves\ExternalApiBundle\Services\Jira\TrustedLandlordJiraService;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase as Base;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;

class TrustedLandlordJiraServiceCase extends Base
{
    use CreateSystemMocksExtensionTrait;

    /**
     * @test
     */
    public function shouldSuccessfullyAddToQueue()
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setFirstName('TestFirst');
        $trustedLandlord->setLastName('TestLast');
        $checkMailingAddress = new CheckMailingAddress();
        $trustedLandlord->setCheckMailingAddress($checkMailingAddress);
        $checkMailingAddress->setCity('TestCity');
        $checkMailingAddress->setIndex('TestIndex');
        $checkMailingAddress->setZip('TestZip');
        $issue = $this->getIssueMock();
        $issue->expects($this->once())
            ->method('getKey')
            ->willReturn('RTT-22');

        $jiraClientMock = $this->getJiraClientMock();
        $jiraClientMock->expects($this->once())
            ->method('createIssue')
            ->with($trustedLandlord->getFullName() . " Verification", $checkMailingAddress->getFullAddress())
            ->willReturn($issue);

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->once())
            ->method('persist');

        $emMock->expects($this->once())
            ->method('flush');

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('debug')
            ->with('Created issue#RTT-22 for trustedLandlordRecord#');

        $trustedLandlordJiraService = new TrustedLandlordJiraService(
            $this->getTrustedLandlordStatusManagerMock(),
            $jiraClientMock,
            $emMock,
            $logger
        );

        $result = $trustedLandlordJiraService->addToQueue($trustedLandlord);
        $this->assertEquals(
            'RTT-22',
            $result->getJiraKey(),
            'We should create new jira mapping'
        );
    }

    /**
     * @test
     */
    public function shouldSuccessfullyHandleWebhookEvent()
    {
        $trustedLandlordJiraService = $this->getTrustedLandlordStatusManagerMock();
        $trustedLandlordJiraService->expects($this->once())
            ->method('updateStatus');

        $mapping = new TrustedLandlordJiraMapping();
        $trusted = new TrustedLandlord();
        $trusted->setJiraMapping($mapping);
        $mapping->setTrustedLandlord($trusted);
        $em = $this->getEntityManagerMock();
        $repository = $this->getEntityRepositoryMock();
        $em->expects($this->once())->method('getRepository')->willReturn($repository);
        $repository->expects($this->once())->method('findOneBy')->willReturn($mapping);

        $trustedLandlordJiraService = new TrustedLandlordJiraService(
            $trustedLandlordJiraService,
            $this->getJiraClientMock(),
            $em,
            $this->getLoggerMock()
        );
        $data = [
            'issue' => ['key' => 'RT-22'],
            'transition' => ['to_status' => 'new']
        ];
        $trustedLandlordJiraService->handleWebhookEvent($data);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTrustedLandlordStatusManagerMock()
    {
        return $this->getBaseMock('RentJeeves\LandlordBundle\Services\TrustedLandlordStatusManager');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJiraClientMock()
    {
        return $this->getBaseMock('RentJeeves\ExternalApiBundle\Services\Jira\JiraClient');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIssueMock()
    {
        return $this->getBaseMock('JiraClient\Resource\Issue');
    }
}

