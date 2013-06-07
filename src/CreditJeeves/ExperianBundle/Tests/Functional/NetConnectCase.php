<?php
namespace CreditJeeves\ExperianBundle\Tests\Functional;

use CreditJeeves\DataBundle\Entity\Settings;
use CreditJeeves\TestBundle\BaseTestCase;
use CreditJeeves\ExperianBundle\NetConnect;
use CreditJeeves\DataBundle\Entity\User;

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
        require_once __DIR__.'/../../../CoreBundle/sfConfig.php';

        $em = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array('getRepository'),
            array(),
            '',
            false
        );

        $settings = new Settings();
        $settings->setNetConnectPassword(\sfConfig::get('experian_net_connect_userpwd'));
        $xmlRoot = \sfConfig::get('experian_net_connect_XML_root');
        $settings->setNetConnectEai($xmlRoot['EAI']);

        $repo = $this->getMock(
            '\Doctrine\ORM\EntityRepository',
            array('find'),
            array(),
            '',
            false
        );

        $repo->expects($this->once())
            ->method('find')
            ->will($this->returnValue($settings));

        $em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repo));


        \sfConfig::set('experian_net_connect_userpwd', '');

        $this->getContainer()->get('experian.net_connect')->initConfigs(
            $this->getContainer()->getParameter('server_name'),
            $em
        );

        $this->assertTrue(is_string($this->getResponseOnUserData($this->user)));
    }
}
