<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional\NetConnect;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\ExperianBundle\NetConnect\CreditProfile;
use CreditJeeves\ExperianBundle\NetConnect\Exception;
use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\DataBundle\Entity\Applicant;
use PHPUnit_Framework_AssertionFailedError;
use Guzzle\Http\Exception\CurlException;

/**
 * NetConnect test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
abstract class CreditProfileCase extends BaseTestCase
{
    protected $creditProfileClass = 'CreditJeeves\ExperianBundle\NetConnect\CreditProfile';

    protected $user = array(
        'Name' => array(
            'Surname' => 'BREEN',
            'First' => 'John',
            'Middle' => 'WAKEFIELD'
        ),
        'SSN' => '666042073',
        'CurrentAddress' => array(
            'Street' => 'PO BOX 445',
            'City' => 'APO',
            'State' => 'AE',
            'Zip' => '09061',
        )
    );

    /**
     * @return CreditProfile
     */
    protected function getNetConnect()
    {
        $class = $this->creditProfileClass;
        /** @var CreditProfile $creditProfile */
        $creditProfile = new $class(
            $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            true,
            $this->getContainer()->getParameter('server_name'),
            $this->getContainer()->getParameter('kernel.logs_dir'),
            $this->getContainer()->getParameter('web.upload.dir')
        );
        $creditProfile->setConfigs(
            $this->getContainer()->getParameter('net_connect.credit_profile.url'),
            $this->getContainer()->getParameter('net_connect.credit_profile.dbhost'),
            $this->getContainer()->getParameter('net_connect.credit_profile.sub_code')
        );

        return $creditProfile;
    }

    /**
     * @param $data
     *
     * @throws Exception
     *
     * @return string
     */
    protected function getResponseOnUserData($data)
    {
        $aplicant = new Applicant();
        $aplicant->setFirstName($data['Name']['First']);
        $aplicant->setLastName($data['Name']['Surname']);
        $aplicant->setMiddleInitial($data['Name']['Middle']);
        $aplicant->setSsn($data['SSN']);
        $address = new Address();
        $address->setStreet($data['CurrentAddress']['Street']);
        $address->setCity($data['CurrentAddress']['City']);
        $address->setArea($data['CurrentAddress']['State']);
        $address->setZip($data['CurrentAddress']['Zip']);
        $address->setUser($aplicant);
        $aplicant->addAddress($address);

        $tries = 6;
        $e = new PHPUnit_Framework_AssertionFailedError('NetConnect fail');
        while ($tries--) {
            try {
                try {
                    $netConnect = $this->getNetConnect();

                    return $netConnect->getResponseOnUserData($aplicant);
                } catch (Exception $e) {
                    if (4000 != $e->getCode()) {
                        throw $e;
                    }
                }
            } catch (CurlException $e) {
                if (302 == $e->getCode()) {
                    break;
                }
            }
        }
        throw $e;
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Generated XML is invalid
     */
    public function getResponseOnUserDataXmlInvalid()
    {
        $data = $this->user;
        $data['Name']['Surname'] = '';
        $this->getResponseOnUserData($data);
    }

    /**
     * @test
     */
    public function getResponseOnUserDataCorrect()
    {
        $this->assertTrue(is_string($this->getResponseOnUserData($this->user)));
    }
}
