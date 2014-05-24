<?php
namespace RentJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\Score;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\TestBundle\BaseTestCase;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class DoctrineCase extends BaseTestCase
{
    /**
     * @test
     */
    public function getReportScore()
    {
        $this->load(true);
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var Tenant $tenant */
        $tenant = $em->getRepository('RjDataBundle:Tenant')
            ->findOneByEmail('tenant11@example.com');

        $this->assertInstanceOf('RentJeeves\CoreBundle\DateTime', $tenant->getCreatedAt());

        $report = new ReportPrequal();
        $report->setUser($tenant);
        $report->setRawData(file_get_contents(__DIR__ . '/../Fixtures/EmilioVantageScore3.arf'));
        $em->persist($report);
        $em->flush($report);

        /** @var Score $score */
        $score = $tenant->getScores()->last();
        $this->assertEquals(583, $score->getScore());
    }
}
