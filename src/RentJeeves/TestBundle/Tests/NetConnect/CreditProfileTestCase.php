<?php
namespace RentJeeves\TestBundle\Tests\NetConnect;

use CreditJeeves\TestBundle\Tests\NetConnect\CreditProfileTestCase as Base;
use RentJeeves\DataBundle\Entity\Tenant;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class CreditProfileTestCase extends Base
{
    /**
     * @var string
     */
    const APP = 'AppRj';

    /**
     * @test
     */
    public function getResponseOnUserData()
    {
        $applicant = new Tenant();
        $applicant->setEmail('tenant11@example.com');
        $applicant->setSsn('666042073');
        $applicant->setFirstName('Ton');
        $applicant->setLastName('Sharp');
        $this->assertStringEqualsFile(
            $this->getContainer()->getParameter('data.dir') . '/experian/netConnect/tenant11.arf',
            $this->getContainer()->get('experian.net_connect')->getResponseOnUserData($applicant)
        );
    }

    /**
     * @test
     *
     * @expectedException \ExperianXmlException
     */
    public function getResponseOnUserDataXmlException()
    {
        $applicant = new Tenant();
        $applicant->setEmail('tenant11@example.com');
        $this->getContainer()->get('experian.net_connect')->getResponseOnUserData($applicant);
    }
}
