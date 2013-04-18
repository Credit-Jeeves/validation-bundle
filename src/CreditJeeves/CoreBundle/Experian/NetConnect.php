<?php
namespace CreditJeeves\CoreBundle\Experian;

use JMS\DiExtraBundle\Annotation as DI;

require_once __DIR__.'/../sfConfig.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/curl/Curl.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/curl/CurlException.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/xml/Xml.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/experian/ExperianException.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/experian/ExperianCurl.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/experian/Experian.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/experian/ExperianXmlException.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/experian/ExperianXml.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/experian/netConnect/NetConnect.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/experian/netConnect/NetConnectCurl.class.php';
require_once __DIR__.'/../../../../vendor/CreditJeevesSf1/lib/experian/netConnect/NetConnectXml.class.php';

/**
 * @DI\Service("core.experian.net_connect")
 */
class NetConnect extends \NetConnect
{
    public function __construct()
    {
    }

    public function execute($container)
    {
        \sfConfig::fill($container->getParameter('experian'), 'global_experian');
        \sfConfig::set('global_host', $container->getParameter('server_name'));
        parent::__construct();
    }
}
