<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Tests\BaseTestCase;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\ExperianBundle\Pidkiq;

/**
 * PIDKIQ test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class LibExperianPidkiqFnTestCase extends BaseTestCase
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
    protected $fixture = array(
        '011_cj_settings.yml',
    );

    /**
     * Tests Pidkiq->getResponseOnUserData()
     */
    protected function getResponseOnUserData($data)
    {
        $pidkiq = new Pidkiq();
        $pidkiq->execute(self::getContainer());

        $aplicant = new User();
        $aplicant->setFirstName($data['Name']['First']);
        $aplicant->setLastName($data['Name']['Surname']);
        $aplicant->setMiddleInitial($data['Name']['Middle']);
        $aplicant->setSsn($data['SSN']);
        $aplicant->setStreetAddress1($data['CurrentAddress']['Street']);
        $aplicant->setCity($data['CurrentAddress']['City']);
        $aplicant->setState($data['CurrentAddress']['State']);
        $aplicant->setZip($data['CurrentAddress']['Zip']);
        $aplicant->setPhone($data['Phone']['Number']);

        return $pidkiq->getResponseOnUserData($aplicant);
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
        $resp = $this->getResponseOnUserData($data);
    }

    /**
     * @test
     * @expectedException \ExperianException
     * @expectedExceptionMessage Unable to standardize current address
     */
    public function getResponseOnUserDataErrorAddressUserPwdFromSettings()
    {
        $this->markTestIncomplete('Implement db settings');
        sfConfig::set('experian_pidkiq_userpwd', '');
        $this->fixture()->loadSymfony('011_cj_settings');

        $event = new sfEvent('Test', 'cj_post_user_init');
        cjContext::getInstance()->getEventDispatcher()->notify($event);

        $data = $this->users[0];
        $data['CurrentAddress']['Zip'] = '99999';
        $resp = $this->getResponseOnUserData($data);
    }

    /**
     * @test
     *
     * @expectedException \ExperianException
     * @expectedExceptionMessage Cannot formulate questions for this consumer.
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
     *
     * @expectedException \ExperianException
     * @expectedExceptionMessage No questions returned due to excessive use
     */
    public function getResponseOnUserDataTimeout()
    {
        $this->getResponseOnUserData($this->users[0]);
    }
}
