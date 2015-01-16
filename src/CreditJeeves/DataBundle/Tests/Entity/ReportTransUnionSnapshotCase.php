<?php

namespace CreditJeeves\DataBundle\Tests\Entity;

use CreditJeeves\DataBundle\Entity\ReportTransunionSnapshot;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\BaseTestCase;

class ReportTransUnionSnapshotCase extends BaseTestCase
{
    public function setUp()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var Tenant $tenant */
        $this->assertNotNull($tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('transU@example.com'));
        $this->assertCount(1, $reports = $tenant->getReportsTUSnapshot());
        $this->assertInstanceOf('CreditJeeves\DataBundle\Entity\ReportTransUnionSnapshot', $report = $reports->first());
        /** @var ReportTransunionSnapshot $report */
        $this->report = $report;
    }

    /**
     * @test
     */
    public function shouldGetBalanceRevolvingAccounts()
    {
        $this->assertEquals('0', $this->report->getBalanceRevolvingAccounts());
    }

    /**
     * @test
     */
    public function shouldGetBalanceMortgageAccounts()
    {
        $this->assertEquals('0', $this->report->getBalanceMortgageAccounts());
    }

    /**
     * @test
     */
    public function shouldGetBalanceInstallmentAccounts()
    {
        $this->assertEquals('276', $this->report->getBalanceInstallmentAccounts());
    }

    /**
     * @test
     */
    public function shouldGetTotalAccounts()
    {
        $this->assertEquals('3', $this->report->getTotalAccounts());
    }

    /**
     * @test
     */
    public function shouldGetTotalOpenAccounts()
    {
        $this->assertEquals('1', $this->report->getTotalOpenAccounts());
    }

    /**
     * @test
     */
    public function shouldGetTotalClosedAccounts()
    {
        $this->assertEquals('2', $this->report->getTotalClosedAccounts());
    }

    /**
     * @test
     */
    public function shouldGetUtilization()
    {
        $this->assertEquals('0', $this->report->getUtilization());
    }

    /**
     * @test
     */
    public function shouldGetNumberOfInquiries()
    {
        $this->assertEquals('4', $this->report->getNumberOfInquiries());
    }

    /**
     * @test
     */
    public function shouldGetDateOfOldestTrade()
    {
        $this->assertEquals('1997-08-01', $this->report->getDateOfOldestTrade());
    }

    /**
     * @test
     */
    public function shouldGetAgeOfCredit()
    {
        $this->assertEquals('17.3', $this->report->getAgeOfCredit());
    }
}
