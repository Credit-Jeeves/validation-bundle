<?php
namespace CreditJeeves\TestBundle\Tests\NetConnect;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\ExperianBundle\NetConnect\Exception;
use CreditJeeves\TestBundle\BaseTestCase;
use RuntimeException;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class CreditProfileTestCase extends BaseTestCase
{
    protected function getAplicant()
    {
        $applicant = new Applicant();
        $applicant->setEmail('mamazza@example.com');
        $applicant->setSsn('666042073');
        $applicant->setFirstName('Ton');
        $applicant->setLastName('Sharp');
        $address = new Address();
        $address->setIsDefault(true);
        $applicant->addAddress($address);

        return $applicant;
    }
    /**
     * @test
     */
    public function getResponseOnUserData()
    {

        $this->assertStringEqualsFile(
            __DIR__ . '/../../Resources/NetConnect/CreditProfile/mamazza.arf',
            $this->getContainer()->get('experian.net_connect.credit_profile')
                ->getResponseOnUserData($this->getAplicant())
        );
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function getResponseOnUserDataException()
    {
        $applicant = $this->getAplicant();
        $applicant->setEmail('some@notExisting.email');
        $this->getContainer()->get('experian.net_connect.credit_profile')->getResponseOnUserData($applicant);
    }

    /**
     * @test
     *
     * @expectedException Exception
     */
    public function getResponseOnUserDataXmlException()
    {
        $applicant = $this->getAplicant();
        $applicant->setFirstName('');
        $this->getContainer()->get('experian.net_connect.credit_profile')->getResponseOnUserData($applicant);
    }
}
