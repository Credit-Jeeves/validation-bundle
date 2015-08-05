<?php

namespace CreditJeeves\DataBundle\Tests\Entity;

use CreditJeeves\DataBundle\Entity\ReportTransunionSnapshot;
use CreditJeeves\DataBundle\Entity\ReportSummaryInterface;
use CreditJeeves\DataBundle\Enum\HardInquiriesPeriod;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\BaseTestCase;

class ReportTransUnionSnapshotCase extends BaseTestCase
{
    /** @var ReportSummaryInterface $report */
    protected $report;

    public function setUp()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var Tenant $tenant */
        $this->assertNotNull($tenant = $em->getRepository('RjDataBundle:Tenant')->findOneByEmail('transU@example.com'));
        $this->assertCount(1, $reports = $tenant->getReportsTUSnapshot());
        $this->assertInstanceOf('CreditJeeves\DataBundle\Entity\ReportTransUnionSnapshot', $report = $reports->first());
        $this->report = $report;
    }

    /**
     * @test
     */
    public function shouldGetBalanceRevolvingAccounts()
    {
        $this->assertEquals('256', $this->report->getBalanceRevolvingAccounts());
    }

    /**
     * @test
     */
    public function shouldGetBalanceMortgageAccounts()
    {
        $this->assertEquals('56000', $this->report->getBalanceMortgageAccounts());
    }

    /**
     * @test
     */
    public function shouldGetBalanceInstallmentAccounts()
    {
        $this->assertEquals('2760', $this->report->getBalanceInstallmentAccounts());
    }

    /**
     * @test
     */
    public function shouldGetBalanceOpenCollectionAccounts()
    {
        $this->assertEquals('100', $this->report->getBalanceOpenCollectionAccounts());
    }

    /**
     * @test
     */
    public function shouldGetTotalMonthlyPayments()
    {
        $this->assertEquals('120', $this->report->getTotalMonthlyPayments());
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
    public function shouldGetTotalDerogatoryAccounts()
    {
        $this->assertEquals('1', $this->report->getTotalDerogatoryAccounts());
    }

    /**
     * @test
     */
    public function shouldGetTotalOpenCollectionAccounts()
    {
        $this->assertEquals('1', $this->report->getTotalOpenCollectionAccounts());
    }

    /**
     * @test
     */
    public function shouldGetTotalPublicRecords()
    {
        $this->assertEquals('21', $this->report->getTotalPublicRecords());
    }

    /**
     * @test
     */
    public function shouldGetUtilization()
    {
        $this->assertEquals('45', $this->report->getUtilization());
    }

    /**
     * @test
     */
    public function shouldGetInquiriesPeriod()
    {
        $this->assertEquals(HardInquiriesPeriod::TWO_YEARS, $this->report->getInquiriesPeriod());
    }

    /**
     * @test
     */
    public function shouldGetOldestTradelineInYears()
    {
        $this->assertEquals('18', $this->report->getOldestTradelineInYears());
    }

    /**
     * @test
     */
    public function getBureauName()
    {
        $this->assertEquals('TransUnion', $this->report->getBureauName());
    }
}
