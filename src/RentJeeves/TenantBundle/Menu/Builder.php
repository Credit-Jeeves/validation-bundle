<?php
namespace RentJeeves\TenantBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('tabs.rent', array('route' => 'tenant_homepage'));
        $menu->addChild('tabs.summary', array('route' => 'tenant_summary'));
        $User = $this->container->get('core.session.tenant')->getUser();
        $isCompleteOrder = $User->isCompleteOrderExist();

        if ($isCompleteOrder) {
            $menu->addChild('tabs.report', array('route' => 'tenant_report'));
        }
        $menu->addChild('tabs.settings', array('route' => 'tenant_password'));

        $sRoute = $this->container->get('request')->get('_route');
        switch ($sRoute) {
            case 'tenant_homepage':
                $menu['tabs.rent']->setAttribute('class', 'active');
                break;
            case 'core_report_get':
            case 'tenant_summary':
                $menu['tabs.summary']->setAttribute('class', 'active');
                break;
            case 'core_report_get_d2c':
            case 'tenant_report':
                $menu['tabs.report']->setAttribute('class', 'active');
                break;
            default:
                $menu['tabs.settings']->setAttribute('class', 'active');
                break;
        }
        return $menu;
    }

    public function settingsMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('settings.password', array('route' => 'tenant_password'));
        $menu->addChild('settings.contact_information', array('route' => 'tenant_contact'));
        $menu->addChild('settings.email', array('route' => 'tenant_email'));
        $menu->addChild('settings.remove', array('route' => 'tenant_remove'));

        $sRoute = $this->container->get('request')->get('_route');
        switch ($sRoute) {
            case 'tenant_password':
                $menu['settings.password']->setUri('');
                break;
            case 'tenant_contact':
                $menu['settings.contact_information']->setUri('');
                break;
            case 'tenant_email':
                $menu['settings.email']->setUri('');
                break;
            case 'tenant_remove':
                $menu['settings.remove']->setUri('');
                break;
        }
        return $menu;
    }
}
