<?php
namespace CreditJeeves\ExperianBundle;

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
 * NetConnect service is used for getting credit reports through API
 *
 * DI\Service("experian.net_connect") It is deffined in services.yml
 */
class NetConnect extends \NetConnect
{
    public function __construct()
    {
    }

    /**
     * DI\InjectParams({ It is deffined in services.yml
     *     "config" = DI\Inject("experian.config"),
     * })
     *
     * @param ExperianConfig $config
     */
    public function initConfigs($config)
    {
        parent::__construct();
    }

    public function execute()
    {
    }
}
