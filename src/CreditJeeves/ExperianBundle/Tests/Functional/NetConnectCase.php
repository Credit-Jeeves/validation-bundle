<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\Settings;
use CreditJeeves\ExperianBundle\ExperianConfig;
use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\DataBundle\Entity\Applicant;
use sfConfig;
use PHPUnit_Framework_AssertionFailedError;
use ExperianException;
use CurlException;

/**
 * NetConnect test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class NetConnectCase extends BaseTestCase
{

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
     * Tests NetConnect->getResponseOnUserData()
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
                    $netConnect = $this->getContainer()->get('experian.net_connect');
                    return $netConnect->getResponseOnUserData($aplicant);
                } catch (ExperianException $e) {
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
     * @expectedException ExperianException
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
