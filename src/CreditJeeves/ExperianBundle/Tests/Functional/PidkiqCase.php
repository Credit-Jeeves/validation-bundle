<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Address;
use CreditJeeves\DataBundle\Entity\Settings;
use CreditJeeves\ExperianBundle\Model\NetConnectResponse;
use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\DataBundle\Entity\Applicant;
use CreditJeeves\ExperianBundle\Pidkiq;

/**
 * PIDKIQ test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class PidkiqCase extends BaseTestCase
{

    protected $users = array(
        0 => array(
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
            'DOB' => '',
            'YOB' => '',
            'MothersMaidenName' => '',
            'EmailAddress' => '',
        ),
        1 => array(
            'Name' => array(
                'Surname' => 'KURTH',
                'First' => 'brian',
                'Middle' => 'P',
                'Gen' => '',
            ),
            'SSN' => '666810987',
            'CurrentAddress' => array(
                'Street' => '2010 SAINT NAZAIRE BLVD',
                'City' => 'HOMESTEAD',
                'State' => 'FL',
                'Zip' => '33039',
            ),
            'PreviousAddress' => '',
            'Phone' => array(
                'Number' => '',
                'Type' => '',
            ),
            'Employment' => '',
            'Age' => '',
            'DOB' => '',
            'YOB' => '',
            'MothersMaidenName' => '',
            'EmailAddress' => '',
        ),
        2 => array(
            'Name' => array(
                'Surname' => 'Caputo',
                'First' => 'Julie',
                'Middle' => 'a',
                'Gen' => '',
            ),
            'SSN' => '666724863',
            'CurrentAddress' => array(
                'Street' => '1903 INGERSOL PL',
                'City' => 'NEW PORT RICHEY',
                'State' => 'FL',
                'Zip' => '33552',
            ),
            'PreviousAddress' => '',
            'Phone' => array(
                'Number' => '',
                'Type' => '',
            ),
            'Employment' => '',
            'Age' => '',
            'DOB' => '',
            'YOB' => '',
            'MothersMaidenName' => '',
            'EmailAddress' => '',
        )
    );

    protected function getApplicant($data)
    {
        $aplicant = new Applicant();
        $aplicant->setFirstName($data['Name']['First']);
        $aplicant->setLastName($data['Name']['Surname']);
        $aplicant->setMiddleInitial($data['Name']['Middle']);
        $aplicant->setSsn($data['SSN']);
        $aplicant->setPhone($data['Phone']['Number']);
        $address = new Address();
        $address->setStreet($data['CurrentAddress']['Street']);
        $address->setCity($data['CurrentAddress']['City']);
        $address->setArea($data['CurrentAddress']['State']);
        $address->setZip($data['CurrentAddress']['Zip']);
        $address->setUser($aplicant);
        $address->setIsDefault(true);
        $aplicant->addAddress($address);

        return $aplicant;
    }

    protected function getPidkiq()
    {
        $pidkiq = new Pidkiq();
        $pidkiq->initConfigs(
            $this->getContainer()->get('experian.config'),
            $this->getContainer()->getParameter('experian.logging'),
            $this->getContainer()->getParameter('kernel.logs_dir')
        );
        return $pidkiq;
    }

    /**
     * Tests Pidkiq->getResponseOnUserData()
     */
    protected function getResponseOnUserData($data)
    {
        $aplicant = $this->getApplicant($data);

        return $this->getPidkiq()->getResponseOnUserData($aplicant);
    }

    /**
     * @test
     * @expectedException \ExperianException
     * @expectedExceptionMessage Unable to standardize current address
     */
    public function getResponseOnUserDataErrorAddress()
    {
        $data = $this->users[0];
        $data['CurrentAddress']['Zip'] = '99999';
        $this->getResponseOnUserData($data);
    }

    /**
     * @test
     *
     * @expectedException \ExperianException
     * @expectedExceptionMessage Consumer Not Found on File One
     */
    public function getResponseOnUserDataIncorrect()
    {
        $data = $this->users[0];
        $data['Name']['Surname'] = 'dfgsdfgsdfg';
        $data['Name']['First'] = 'dfg';
        $this->getResponseOnUserData($data);
    }

    /**
     * @test
     */
    public function getResponseOnUserDataCorrect()
    {
        $i = 0;
        while (!empty($this->users[$i])) {
            try {
                $resp = $this->getResponseOnUserData($this->users[$i]);
                $this->assertTrue(is_array($resp));
                return;
            } catch (\ExperianException $e) {
                if ('No questions returned due to excessive use' == $e->getMessage()) {
                    $i++;
                    continue;
                }
                throw $e;
            }
        }

        $this->fail("All '{$i}' tries executed or functional is broken");
    }

    /**
     * @test
     * does not work anymore. Ton
     * 2013.03.22 It works again
     * 2013.03.29 It does not work
     * 2013.04.29 It works again
     * 2013.05.05 It does not work
     * 2013.05.15 It works again
     * 2013.06.12 It does not work
     * 2013.06.17 It works again
     * 2014.04.18 It does not work
     *
     * @expectedException \ExperianException
     * @expectedExceptionMessage No questions returned due to excessive use
     */
    public function getResponseOnUserDataTimeout()
    {
        $this->getResponseOnUserData($this->users[0]);
    }
}
