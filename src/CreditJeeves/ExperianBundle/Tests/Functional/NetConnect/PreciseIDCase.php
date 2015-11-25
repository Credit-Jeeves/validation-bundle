<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional\NetConnect;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use CreditJeeves\ExperianBundle\NetConnect\Exception;
use CreditJeeves\ExperianBundle\NetConnect\PreciseID;
use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\DataBundle\Entity\Applicant;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
abstract class PreciseIDCase extends BaseTestCase
{
    protected $preciseIDClass = 'CreditJeeves\ExperianBundle\NetConnect\PreciseID';

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
            'DOB' => '1957-02-19',
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
            'DOB' => '1957-02-19',
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
            'DOB' => '1959-01-20',
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

    /**
     * @test
     */
    public function getObjectOnUserData()
    {
        $netConnectResponse = $this->getContainer()->get('experian.net_connect.precise_id')
            ->getObjectOnUserData($this->getApplicant($this->users[0]));

        $this->assertEquals(
            232,
            $netConnectResponse->getProducts()->getPreciseIDServer()->getSummary()->getPreciseIDScore()
        );
    }

    /**
     * @test
     */
    public function getResponseOnUserData()
    {
        $this->assertEquals(
            array(
                'Which of the following is the highest level of education you completed?' => array(
                    '1' => 'HIGH SCHOOL DIPLOMA',
                    '2' => 'SOME COLLEGE',
                    '3' => 'BACHELOR DEGREE',
                    '4' => 'GRADUATE DEGREE',
                    '5' => 'NONE OF THE ABOVE',
                ),
                'You may have opened a student loan in or around February 2006. ' .
                'To whom do you make your payments?' => array(
                    '1' => 'FIRST MIDWEST BK',
                    '2' => 'VSAC LOAN SERVICES',
                    '3' => 'COMMERCE BANK',
                    '4' => 'US BANK',
                    '5' => 'NONE OF THE ABOVE/DOES NOT APPLY',
                ),
                'To which of the following professions do you currently or have previously belonged?' => array(
                    '1' => 'ACCOUNTANT',
                    '2' => 'NURSE',
                    '3' => 'CHIROPRACTOR',
                    '4' => 'PROFESSIONAL DRIVER',
                    '5' => 'NONE OF THE ABOVE',
                ),
                'Based on our records, you opened an auto loan/lease around June 2000. ' .
                'Please select the dollar range of your total monthly payment.' => array(
                    '1' => '$50 - $99',
                    '2' => '$100 - $149',
                    '3' => '$150 - $199',
                    '4' => '$200 - $249',
                    '5' => 'NONE OF THE ABOVE/DOES NOT APPLY',
                ),

            ),
            $this->getContainer()->get('experian.net_connect.precise_id')
                ->getResponseOnUserData($this->getApplicant($this->users[0]))
        );
    }

    /**
     * @return PreciseID
     */
    protected function getPreciseID()
    {
        $class = $this->preciseIDClass;
        /** @var PreciseID $preciseID */
        $preciseID = new $class(
            $this->getContainer()->get('doctrine.orm.default_entity_manager'),
            true,
            $this->getContainer()->getParameter('server_name'),
            $this->getContainer()->getParameter('kernel.logs_dir'),
            $this->getContainer()->getParameter('web.upload.dir')
        );
        $preciseID->setConfigs(
            $this->getContainer()->getParameter('net_connect.precise_id.url'),
            $this->getContainer()->getParameter('net_connect.precise_id.dbhost'),
            $this->getContainer()->getParameter('net_connect.precise_id.sub_code')
        );

        return $preciseID;
    }

    /**
     * Tests PreciseID->getResponseOnUserData()
     */
    protected function execute($data)
    {
        $aplicant = $this->getApplicant($data);

        return $this->getPreciseID()->getResponseOnUserData($aplicant);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionMessage Unable to standardize current address
     */
    public function getResponseOnUserDataErrorAddress()
    {
        $data = $this->users[0];
        $data['CurrentAddress']['Zip'] = '99999';
        $this->execute($data);
    }

    /**
     * @test
     *
     * @expectedException Exception
     * @expectedExceptionMessage Generated XML is invalid
     */
    public function getResponseOnUserDataXMLIsInvalid()
    {
        $data = $this->users[0];
        $data['Name']['Surname'] = '';
        $this->execute($data);
    }

    /**
     * @test
     *
     * @expectedException Exception
     * @expectedExceptionMessage Cannot formulate questions for this consumer.
     */
    public function getResponseOnUserDataIncorrect()
    {
        $data = $this->users[0];
        $data['Name']['Surname'] = 'dfgsdfgsdfg';
        $data['Name']['First'] = 'dfg';
        $this->execute($data);
    }

    /**
     * @test
     */
    public function getResponseOnUserDataCorrect()
    {
        $i = 0;
        while (!empty($this->users[$i])) {
            try {
                $resp = $this->execute($this->users[$i]);
                $this->assertTrue(is_array($resp));

                return;
            } catch (Exception $e) {
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
     * @depends getResponseOnUserDataCorrect
     * does not work anymore. Ton
     * 2013.03.22 It works again
     * 2013.03.29 It does not work
     * 2013.04.29 It works again
     * 2013.05.05 It does not work
     * 2013.05.15 It works again
     * 2013.06.12 It does not work
     * 2013.06.17 It works again
     * 2014.04.18 It does not work
     * It works again
     * 2014.05.15 It does not work
     * 2014.05.28 It works again
     *
     *
     * @expectedException Exception
     * @expectedExceptionMessage No questions returned due to excessive use
     */
    public function getResponseOnUserDataTimeout()
    {
        $this->execute($this->users[0]);
    }
}
