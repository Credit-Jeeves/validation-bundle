<?php
namespace CreditJeeves\DataBundle\Tests\EventListener;

use CreditJeeves\CoreBundle\Enum\ScoreModelType;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\TestBundle\BaseTestCase;

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
        /** @var Applicant $applicant */
        $applicant = $em->getRepository('DataBundle:Applicant')
            ->findOneByEmail('marion@example.com');

        $report = new ReportPrequal();
        $report->setUser($applicant);
        $report->setRawData(file_get_contents(__DIR__ . '/../Fixtures/marion.arf'));

        $score = $report->getArfReport()->getScore();

        $this->assertEquals(535, $score);
    }
}
