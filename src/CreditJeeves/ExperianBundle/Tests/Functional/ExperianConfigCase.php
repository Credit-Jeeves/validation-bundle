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
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class ExperianConfigCase extends BaseTestCase
{
    /**
     * If it is fail all experian libs will fail too
     *
     * @test
     */
    public function constructor()
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
        $settings->setNetConnectPassword(sfConfig::get('experian_net_connect_userpwd'));
        $xmlRoot = sfConfig::get('experian_net_connect_XML_root');
        $settings->setNetConnectEai($xmlRoot['EAI']);

        $settings->setPidkiqPassword(sfConfig::get('experian_pidkiq_userpwd'));
        $xmlRoot = sfConfig::get('experian_pidkiq_XML_root');
        $settings->setPidkiqEai($xmlRoot['EAI']);

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


        sfConfig::set('experian_net_connect_userpwd', '');
        $netConnectXMLRoot = sfConfig::get('experian_net_connect_XML_root');
        $netConnectXMLRoot['EAI'] = '';
        sfConfig::set('experian_net_connect_XML_root', $netConnectXMLRoot);

        sfConfig::set('experian_pidkiq_userpwd', '');
        $pidkiqXMLRoot = sfConfig::get('experian_pidkiq_XML_root');
        $pidkiqXMLRoot['EAI'] = '';
        sfConfig::set('experian_pidkiq_XML_root', $pidkiqXMLRoot);
        new ExperianConfig('soemUrl', $em, false);

        $this->assertNotEmpty(sfConfig::get('experian_net_connect_userpwd'));
        $netConnectXMLRoot = sfConfig::get('experian_net_connect_XML_root');
        $this->assertNotEmpty($netConnectXMLRoot['EAI']);
        $this->assertNotEmpty(sfConfig::get('experian_pidkiq_userpwd'));
        $pidkiqXMLRoot = sfConfig::get('experian_pidkiq_XML_root');
        $this->assertNotEmpty($pidkiqXMLRoot['EAI']);
    }
}
