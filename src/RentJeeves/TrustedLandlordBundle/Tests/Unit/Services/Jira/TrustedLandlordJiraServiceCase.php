<?php

namespace RentJeeves\TrustedLandlordBundle\Tests\Unit\Services\Jira;

use RentJeeves\DataBundle\Entity\CheckMailingAddress;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Entity\TrustedLandlordJiraMapping;
use RentJeeves\TrustedLandlordBundle\Model\Jira\Fields;
use RentJeeves\TrustedLandlordBundle\Model\Jira\Issue;
use RentJeeves\TrustedLandlordBundle\Model\Jira\Transition;
use RentJeeves\TrustedLandlordBundle\Model\Jira\TrustedLandlordIssue;
use RentJeeves\TrustedLandlordBundle\Services\Jira\TrustedLandlordJiraService;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase as Base;
use RentJeeves\TestBundle\Traits\CreateSystemMocksExtensionTrait;
use RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordService;
use  RentJeeves\TrustedLandlordBundle\Model\Jira\Type;

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
            ->method('createTrustedLandlordIssue')
            ->with($trustedLandlord)
            ->willReturn($issue);

        $emMock = $this->getEntityManagerMock();
        $emMock->expects($this->once())
            ->method('persist');

        $emMock->expects($this->once())
            ->method('flush');

        $logger = $this->getLoggerMock();
        $logger->expects($this->once())
            ->method('debug');

        $trustedLandlordJiraService = new TrustedLandlordJiraService(
            $this->getBaseMock(TrustedLandlordService::class),
            $jiraClientMock,
            $emMock,
            $logger,
            $this->getSerializerMock()
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
        $trustedLandlordService = $this->getBaseMock(TrustedLandlordService::class);
        $trustedLandlordService->expects($this->once())
            ->method('update');

        $mapping = new TrustedLandlordJiraMapping();
        $trusted = new TrustedLandlord();
        $trusted->setJiraMapping($mapping);
        $mapping->setTrustedLandlord($trusted);
        $em = $this->getEntityManagerMock();
        $repository = $this->getEntityRepositoryMock();
        $em->expects($this->once())->method('getRepository')->willReturn($repository);
        $repository->expects($this->once())->method('findOneBy')->willReturn($mapping);
        $serializer = $this->getSerializerMock();
        $trustedLandlordIssue = new TrustedLandlordIssue();
        $trustedLandlordIssue->setTransition(new Transition());
        $trustedLandlordIssue->setIssue(new Issue());
        $trustedLandlordIssue->getIssue()->setFields($fields = new Fields());
        $fields->setType(new Type());
        $serializer->expects($this->once())->method('deserialize')->willReturn($trustedLandlordIssue);
        $trustedLandlordService = new TrustedLandlordJiraService(
            $trustedLandlordService,
            $this->getJiraClientMock(),
            $em,
            $this->getLoggerMock(),
            $serializer
        );
        $data = [
            'issue' => ['key' => 'RT-22'],
            'transition' => ['to_status' => 'new']
        ];
        $trustedLandlordService->handleWebhookEvent($data);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTrustedLandlordStatusManagerMock()
    {
        return $this->getBaseMock('RentJeeves\TrustedLandlordBundle\Services\TrustedLandlordStatusManager');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getJiraClientMock()
    {
        return $this->getBaseMock('RentJeeves\TrustedLandlordBundle\Services\Jira\JiraClient');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIssueMock()
    {
        return $this->getBaseMock('JiraClient\Resource\Issue');
    }
}
