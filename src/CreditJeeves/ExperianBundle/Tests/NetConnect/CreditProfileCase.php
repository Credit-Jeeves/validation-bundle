<?php
namespace CreditJeeves\ExperianBundle\Tests;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\ExperianBundle\NetConnect\Exception;
use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\ExperianBundle\NetConnect\PreciseID;
use CreditJeeves\DataBundle\Entity\Applicant;
use RentJeeves\CoreBundle\DateTime;

/**
 * PreciseID (PIDKIQ) test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class CreditProfileCase extends BaseTestCase
{
    /**
     * @var PreciseID
     */
    protected $objInstance;

    protected function setUp()
    {
        $this->objInstance = $this->getMock(
            'CreditJeeves\ExperianBundle\NetConnect\CreditProfile',
            array('composeRequest', 'doRequest', 'initNetConnectUrl'),
            array(),
            '',
            false
        );
    }

    /**
     * @return Applicant
     */
    protected function getUser()
    {
        $aplicant = new Applicant();
        $aplicant->setFirstName('MARION');
        $aplicant->setLastName('BRIEN');
        $aplicant->setMiddleInitial('R');
        $aplicant->setSsn('666560093');
        $address = new Address();
        $address->setStreet('INGLEWOOD 934 S');
        $address->setCity('INGLEWOOD');
        $address->setArea('CA');
        $address->setZip('903013646');
        $aplicant->addAddress($address);
        $aplicant->setPhone('7818945369');
        $aplicant->setDateOfBirth(new DateTime('1970-01-01'));

        return $aplicant;
    }

    /**
     * @test
     */
    public function getResponseOnUserDataAddUserToRequest()
    {
        $this->objInstance
            ->expects($this->once())
            ->method('composeRequest')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            __DIR__ . '/../../Resources/NetConnect/CreditProfile-Request.xml',
                            $xml
                        );

                        return true;
                    }
                )
            );
        $this->objInstance
            ->expects($this->once())
            ->method('doRequest')
            ->will(
                $this->returnValue(
                    file_get_contents(__DIR__ . '/../../Resources/NetConnect/CreditProfile-Response.xml')
                )
            );
        $this->objInstance->getNetConnectRequest()
            ->getRequest()->getProducts()->getCreditProfile()->getSubscriber()->setSubCode('2266580');
        $this->objInstance->getNetConnectRequest()
            ->setDbHost('STAR')
            ->setEai('HRPCX4RA');
        $this->assertStringEqualsFile(
            __DIR__ . '/../../Resources/ARF/marion.arf',
            $this->objInstance->getResponseOnUserData($this->getUser())
        );
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage No record found
     */
    public function getResponseOnUserDataNoRecordFound()
    {
        $this->objInstance
            ->expects($this->once())
            ->method('doRequest')
            ->will(
                $this->returnValue(
                    file_get_contents(__DIR__ . '/../../Resources/NetConnect/CreditProfile-Response-NoRecordFound.xml')
                )
            );

        $this->objInstance->getResponseOnUserData($this->getUser());
    }
}
