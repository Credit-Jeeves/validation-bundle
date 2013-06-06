<?php
namespace CreditJeeves\ExperianBundle;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

require_once __DIR__.'/../CoreBundle/sfConfig.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/curl/Curl.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/curl/CurlException.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/xml/Xml.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/ExperianException.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/ExperianCurl.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/Experian.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/ExperianXmlException.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/ExperianXml.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/netConnect/NetConnect.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/netConnect/NetConnectCurl.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/netConnect/NetConnectXml.class.php';

/**
 * @DI\Service("experian.net_connect")
 */
class NetConnect extends \NetConnect
{
    public function __construct()
    {
    }

    /**
     * @DI\InjectParams({
     *     "serverName" = @DI\Inject("%server_name%"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     * })
     *
     * @param string $serverName
     * @param EntityManager $em
     */
    public function initConfigs($serverName, EntityManager $em)
    {
        \sfConfig::set('global_host', $serverName);
        /** @var \CreditJeeves\DataBundle\Entity\Settings $settings */
        $settings = $em->getRepository('DataBundle:Settings')->find(1);

        if (empty($settings)) {
            return;
        }

        \sfConfig::set('experian_net_connect_userpwd', $settings->getNetConnectPassword());
        $xmlRoot = \sfConfig::get('experian_net_connect_XML_root');
        $xmlRoot['EAI'] = $settings->getNetConnectEai();
        \sfConfig::set('experian_net_connect_XML_root', $xmlRoot);
    }

    public function execute()
    {
        parent::__construct();
    }
}
