<?php
namespace CreditJeeves\ExperianBundle;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

require_once __DIR__.'/../CoreBundle/sfConfig.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/curl/Curl.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/curl/CurlException.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/xml/Xml.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/ExperianException.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/ExperianCurl.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/Experian.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/ExperianXmlException.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/ExperianXml.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/netConnect/NetConnect.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/netConnect/NetConnectCurl.class.php';
require_once __DIR__.'/../../../vendor/credit-jeeves/credit-jeeves/lib/experian/netConnect/NetConnectXml.class.php';

/**
 * NetConnect is used for getting credit reports through API
 *
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
