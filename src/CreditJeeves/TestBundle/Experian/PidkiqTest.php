<?php
namespace CreditJeeves\TestBundle\Experian;

use JMS\DiExtraBundle\Annotation as DI;

require_once __DIR__ . '/../../ExperianBundle/Pidkiq.php';
require_once __DIR__ . '/../../../../vendor/CreditJeevesSf1/lib/experian/pidkiq/PidkiqTest.class.php';

/**
 * @DI\Service("experian.pidkiq")
 */
class PidkiqTest extends \PidkiqTest
{
    public function __construct()
    {
    }

    public function execute($container)
    {
        \sfConfig::set('global_host', $container->getParameter('server_name'));
        parent::__construct();
    }
}
