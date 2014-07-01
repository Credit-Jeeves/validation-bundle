<?php
namespace CreditJeeves\TestBundle\Tests\Experian;

use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Entity\Score;
use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\TestBundle\BaseTestCase;
use RuntimeException;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class NetConnectTestCase extends BaseTestCase
{
    /**
     * @test
     */
    public function getResponseOnUserData()
    {
        $applicant = new Applicant();
        $applicant->setEmail('mamazza@example.com');
        $applicant->setSsn('666042073');
        $applicant->setFirstName('Ton');
        $applicant->setLastName('Sharp');
        $this->assertStringEqualsFile(
            $this->getContainer()->getParameter('data.dir') . '/experian/netConnect/mamazza.arf',
            $this->getContainer()->get('experian.net_connect')->getResponseOnUserData($applicant)
        );
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function getResponseOnUserDataException()
    {
        $this->getContainer()->get('experian.net_connect')->getResponseOnUserData(new Applicant());
    }

    /**
     * @test
     *
     * @expectedException \ExperianXmlException
     */
    public function getResponseOnUserDataXmlException()
    {
        $applicant = new Applicant();
        $applicant->setEmail('emilio@example.com');
        $this->getContainer()->get('experian.net_connect')->getResponseOnUserData($applicant);
    }
}
