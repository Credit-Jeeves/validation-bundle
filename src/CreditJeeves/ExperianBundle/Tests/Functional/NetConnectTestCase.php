<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\CoreBundle\Tests\BaseTestCase;
use CreditJeeves\ExperianBundle\NetConnect;
use CreditJeeves\DataBundle\Entity\User;

/**
 * NetConnect test case.
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class LibExperianNetConnectFnTestCase extends BaseTestCase
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
     * Tests Pidkiq->getResponseOnUserData()
     */
    protected function getResponseOnUserData($data)
    {
        $netConnect = new NetConnect();
        $netConnect->execute(self::getContainer());

        $aplicant = new User();
        $aplicant->setFirstName($data['Name']['First']);
        $aplicant->setLastName($data['Name']['Surname']);
        $aplicant->setMiddleInitial($data['Name']['Middle']);
        $aplicant->setSsn($data['SSN']);
        $aplicant->setStreetAddress1($data['CurrentAddress']['Street']);
        $aplicant->setCity($data['CurrentAddress']['City']);
        $aplicant->setState($data['CurrentAddress']['State']);
        $aplicant->setZip($data['CurrentAddress']['Zip']);

        return $netConnect->getResponseOnUserData($aplicant);
    }

    /**
     * @test
     * @expectedException \ExperianException
     * @expectedExceptionMessage Generated XML is invalid
     */
    public function getResponseOnUserDataXmlInvalid()
    {
        $data = $this->user;
        $data['Name']['Surname'] = '';
        $this->getResponseOnUserData($data);
    }

    /**
     * @ Experian does not response as it should be test
     *
     * @expectedException \ExperianException
     * @expectedExceptionMessage Cannot formulate questions for this consumer.
     */
    public function getResponseOnUserDataIncorrect()
    {
        $data = $this->user;
        $data['Name']['Surname'] = 'dfgsdfgsdfg';
        $data['Name']['First'] = 'dfg';
        $resp = $this->getResponseOnUserData($data);
    }

    /**
     * @test
     */
    public function getResponseOnUserDataCorrect()
    {
        $this->assertTrue(is_string($this->getResponseOnUserData($this->user)));
    }

    /**
     * @test
     */
    public function getResponseOnUserDataCorrectFromSettings()
    {
        $this->markTestIncomplete('Implement db settings');
        sfConfig::set('experian_net_connect_userpwd', '');
        $this->fixture()->loadSymfony('011_cj_settings');

        $event = new sfEvent('Test', 'cj_post_user_init');
        cjContext::getInstance()->getEventDispatcher()->notify($event);

        $this->assertTrue(is_string($this->getResponseOnUserData($this->user)));
    }
}
