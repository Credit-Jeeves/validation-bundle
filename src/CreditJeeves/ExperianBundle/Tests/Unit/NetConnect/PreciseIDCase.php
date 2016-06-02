<?php
namespace CreditJeeves\ExperianBundle\Tests\Unit\NetConnect;

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
class PreciseIDCase extends BaseTestCase
{
    /**
     * @var PreciseID
     */
    protected $objInstance;

    protected function setUp()
    {
        $this->objInstance = $this->getMock(
            'CreditJeeves\ExperianBundle\NetConnect\PreciseID',
            array('doRequest'),
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
            ->method('doRequest')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            __DIR__ . '/../../Data/NetConnect/PreciseID-Request.xml',
                            $xml
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    file_get_contents(__DIR__ . '/../../Data/NetConnect/PreciseID-Response.xml')
                )
            );
        $this->objInstance->getNetConnectRequest()
            ->getRequest()->getProducts()->getPreciseIDServer()->getSubscriber()->setSubCode('2279720');
        $this->objInstance->getNetConnectRequest()
            ->setDbHost('PRECISE_ID_TEST')
            ->setEai('HRPCX4RA');
        $this->objInstance->getResponseOnUserData($this->getUser());
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Cannot formulate questions for this consumer.
     */
    public function getResponseOnUserDataCannotFormulateQuestions()
    {
        $this->objInstance
            ->expects($this->once())
            ->method('doRequest')
            ->will(
                $this->returnValue(
                    file_get_contents(
                        __DIR__ . '/../../Data/NetConnect/PreciseID-Response-CannotFormulateQuestions.xml'
                    )
                )
            );
        $this->objInstance->getResponseOnUserData($this->getUser());
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage No questions returned due to excessive use
     */
    public function getResponseOnUserDataNoQuestionsReturnedDueToExcessiveUse()
    {
        $this->objInstance
            ->expects($this->once())
            ->method('doRequest')
            ->will(
                $this->returnValue(
                    file_get_contents(
                        __DIR__ .
                        '/../../Data/NetConnect/PreciseID-Response-NoQuestionsReturnedDueToExcessiveUse.xml'
                    )
                )
            );
        $this->objInstance->getResponseOnUserData($this->getUser());
    }

    /**
     * FIXME need message
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage 015000 0300
     */
    public function getResponseOnUserDataDeceased()
    {
        $this->objInstance
            ->expects($this->once())
            ->method('doRequest')
            ->will(
                $this->returnValue(
                    file_get_contents(
                        __DIR__ .
                        '/../../Data/NetConnect/PreciseID-Response-Deceased.xml'
                    )
                )
            );
        $this->objInstance->getResponseOnUserData($this->getUser());
    }

    /**
     * @test
     */
    public function getResultRequest()
    {
        $this->objInstance
            ->expects($this->once())
            ->method('doRequest')
            ->with(
                $this->callback(
                    function ($xml) {
                        $this->assertStringEqualsFile(
                            __DIR__ . '/../../Data/NetConnect/PreciseID-Questions-Request.xml',
                            $xml
                        );

                        return true;
                    }
                )
            )
            ->will(
                $this->returnValue(
                    file_get_contents(__DIR__ . '/../../Data/NetConnect/PreciseID-Questions-Response.xml')
                )
            );
        $this->objInstance->getNetConnectRequest()
            ->setDbHost('PRECISE_ID_TEST')
            ->setEai('HRPCX4RA');
        $this->assertTrue(
            $this->objInstance->getResult(
                '1BF7168380E8DB40CA9BE5D14F32F347.pidd1v-1408261641330210446354688',
                array(2, 3, 3, 5)
            )
        );
    }

    /**
     * @test
     */
    public function getResultRequestFalse()
    {
        $this->objInstance
            ->expects($this->once())
            ->method('doRequest')
            ->will(
                $this->returnValue(
                    file_get_contents(__DIR__ . '/../../Data/NetConnect/PreciseID-Questions-Response-Wrong.xml')
                )
            );
        $this->assertFalse(
            $this->objInstance->getResult(
                '1BF7168380E8DB40CA9BE5D14F32F347.pidd1v-1408261641330210446354688',
                array(1, 1, 1, 1)
            )
        );
    }
}
