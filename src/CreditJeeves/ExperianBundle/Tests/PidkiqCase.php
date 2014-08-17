<?php
namespace CreditJeeves\ExperianBundle\Tests;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\ExperianBundle\Pidkiq;
use CreditJeeves\DataBundle\Entity\Applicant;
use RentJeeves\CoreBundle\DateTime;

/**
 * PIDKIQ test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class PidkiqCase extends BaseTestCase
{
    /**
     * @var Pidkiq
     */
    protected $objInstance;

    protected $userData = array(
        'Name' => array(
            'Surname' => 'BREEN',
            'First' => 'John',
            'Middle' => 'WAKEFIELD',
            'Gen' => '',
        ),
        'SSN' => '666042073',
        'CurrentAddress' => array(
            'Street' => 'PO BOX 445',
            'City' => 'APO',
            'State' => 'AE',
            'Zip' => '09061',
        ),
        'PreviousAddress' => '',
        'Phone' => array(
            'Number' => '9137644215',
            'Type' => '',
        ),
        'Employment' => '',
        'Age' => '',
        'DOB' => '02191957',
        'YOB' => '',
        'MothersMaidenName' => '',
        'EmailAddress' => '',
    );

    protected function setUp()
    {
        $this->objInstance = $this->getMock('CreditJeeves\ExperianBundle\Pidkiq', null, array(), '', false);
    }

    /**
     * @return Applicant
     */
    protected function getAplicant()
    {
        $aplicant = new Applicant();
        $aplicant->setFirstName($this->userData['Name']['First']);
        $aplicant->setLastName($this->userData['Name']['Surname']);
        $aplicant->setMiddleInitial($this->userData['Name']['Middle']);
        $aplicant->setSsn($this->userData['SSN']);
        $address = new Address();
        $address->setStreet($this->userData['CurrentAddress']['Street']);
        $address->setCity($this->userData['CurrentAddress']['City']);
        $address->setArea($this->userData['CurrentAddress']['State']);
        $address->setZip($this->userData['CurrentAddress']['Zip']);
        $aplicant->addAddress($address);
        $aplicant->setPhone($this->userData['Phone']['Number']);
        $aplicant->setDateOfBirth(new DateTime('1957-02-19'));

        return $aplicant;
    }

    /**
     * Tests Pidkiq->modelToData()
     *
     * @test
     */
    public function modelToData()
    {
        $this->assertEquals($this->userData, $this->objInstance->modelToData($this->getAplicant()));
    }
}

