<?php
namespace CreditJeeves\ExperianBundle;

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
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/pidkiq/Pidkiq.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/pidkiq/PidkiqCurl.class.php';
require_once __DIR__.'/../../../vendor/CreditJeevesSf1/lib/experian/pidkiq/PidkiqXml.class.php';

/**
 * @DI\Service("experian.pidkiq")
 */
class Pidkiq extends \Pidkiq
{
    public function __construct()
    {
    }

    public function execute($container)
    {
        \sfConfig::fill($container->getParameter('experian.pidkiq'), 'global_experian_pidkiq');
        \sfConfig::fill($container->getParameter('experian.netConnect'), 'global_experian_net_connect');
        \sfConfig::set('global_host', $container->getParameter('server_name'));
        parent::__construct();
    }
}
