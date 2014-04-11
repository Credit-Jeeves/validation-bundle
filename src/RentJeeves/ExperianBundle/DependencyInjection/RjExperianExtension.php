<?php

namespace RentJeeves\ExperianBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use sfConfig;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RjExperianExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs = $container->getParameter('experian');
        sfConfig::set('global_experian_pidkiq_userpwd', $configs['pidkiq_userpwd']);
        sfConfig::set('experian_net_connect_userpwd', $configs['net_connect_userpwd']);
        $container->setParameter('experian_pidkiq_userpwd', $configs['pidkiq_userpwd']);
        $container->setParameter('global_experian_net_connect_userpwd', $configs['net_connect_userpwd']);

        $pidkiqXmlContent = sfConfig::get('experian_pidkiq_XML_content');
        $pidkiqXmlContent['Subscriber']['SubCode'] = '2279720';
        sfConfig::set('experian_pidkiq_XML_content', $pidkiqXmlContent);

        $netConnectXmlContent = sfConfig::get('experian_net_connect_XML_content');
        $netConnectXmlContent['Subscriber']['SubCode'] = '2279720';
        unset($netConnectXmlContent['AddOns']['AutoProfileSummary']);
        unset($netConnectXmlContent['AddOns']['DirectCheck']);
        unset($netConnectXmlContent['AddOns']['RiskModels']['ScorexPLUS']);
        $netConnectXmlContent['AddOns']['RiskModels']['VantageScore3'] = 'Y';
        $netConnectXmlContent['OutputType']['ARF'][' Segment130'] = 'Y';
        sfConfig::set('experian_net_connect_XML_content', $netConnectXmlContent);
    }
}
