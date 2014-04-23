<?php
namespace RentJeeves\ExperianBundle;

use CreditJeeves\DataBundle\Entity\Settings;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use sfConfig;
use CreditJeeves\ExperianBundle\ExperianConfig as Base;

/**
 * DI\Service("experian.config") It is deffined in services.yml
 */
class ExperianConfig extends Base
{
    /**
     * @param string $serverName
     * @param EntityManager $em
     * @param bool $isLogging
     */
    public function __construct($serverName, EntityManager $em, $isLogging)
    {

        $netConnectXmlContent = sfConfig::get('experian_net_connect_XML_content');
        unset($netConnectXmlContent['AddOns']['AutoProfileSummary']);
        unset($netConnectXmlContent['AddOns']['DirectCheck']);
        unset($netConnectXmlContent['AddOns']['RiskModels']['ScorexPLUS']);
        $netConnectXmlContent['AddOns']['RiskModels']['VantageScore3'] = 'Y';
        $netConnectXmlContent['OutputType']['ARF']['Segment130'] = 'Y';
        sfConfig::set('experian_net_connect_XML_content', $netConnectXmlContent);

        $pidkiqXmlContent = sfConfig::get('experian_pidkiq_XML_content');
        $pidkiqXmlContent['Options']['AccessChannel'] = 'PPQ';
        sfConfig::set('experian_pidkiq_XML_content', $pidkiqXmlContent);
        parent::__construct($serverName, $em, $isLogging);
    }
}
