<?php
namespace CreditJeeves\ExperianBundle;

use CreditJeeves\DataBundle\Entity\Settings;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use sfConfig;

class ExperianConfig
{
    /**
     * @param string $serverName
     * @param EntityManager $em
     * @param bool $isLogging
     */
    public function __construct($serverName, EntityManager $em, $isLogging)
    {
        sfConfig::set('experian_logging', $isLogging);
        sfConfig::set('global_host', $serverName);
        /** @var Settings $settings */
        $settings = $em->getRepository('DataBundle:Settings')->find(1);

        if (empty($settings)) {
            return;
        }

        sfConfig::set('experian_net_connect_userpwd', $settings->getNetConnectPassword());
        $xmlRoot = sfConfig::get('experian_net_connect_XML_root');
        $xmlRoot['EAI'] = $settings->getNetConnectEai();
        sfConfig::set('experian_net_connect_XML_root', $xmlRoot);

        sfConfig::set('experian_pidkiq_userpwd', $settings->getPidkiqPassword());
        $xmlRoot = sfConfig::get('experian_pidkiq_XML_root');
        $xmlRoot['EAI'] = $settings->getPidkiqEai();
        sfConfig::set('experian_pidkiq_XML_root', $xmlRoot);
    }
}
