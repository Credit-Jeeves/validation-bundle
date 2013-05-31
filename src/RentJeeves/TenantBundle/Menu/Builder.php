<?php
namespace RentJeeves\TenantBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('tabs.action_plan', array('route' => 'tenant_homepage'));
        $menu->addChild('tabs.summary', array('route' => 'tenant_homepage'));
        $User = $this->container->get('core.session.applicant')->getUser();
        $isCompleteOrder = $User->isCompleteOrderExist();

        if ($isCompleteOrder) {
            $menu->addChild('tabs.report', array('route' => 'tenant_homepage'));
        }
        $menu->addChild('tabs.settings', array('route' => 'tenant_homepage'));

        $sRoute = $this->container->get('request')->get('_route');
        switch ($sRoute) {
            case 'tenant_homepage':
                $menu['tabs.action_plan']->setAttribute('class', 'active');
                break;
            case 'tenant_homepage':
                $menu['tabs.summary']->setAttribute('class', 'active');
                break;
            case 'tenant_homepage':
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
        $menu->addChild('settings.password', array('route' => 'applicant_password'));
        $menu->addChild('settings.contact_information', array('route' => 'applicant_contact'));
        $menu->addChild('settings.email', array('route' => 'applicant_email'));
        $menu->addChild('settings.remove', array('route' => 'applicant_remove'));

        $sRoute = $this->container->get('request')->get('_route');
        switch ($sRoute) {
            case 'applicant_password':
                $menu['settings.password']->setUri('');
                break;
            case 'applicant_contact':
                $menu['settings.contact_information']->setUri('');
                break;
            case 'applicant_email':
                $menu['settings.email']->setUri('');
                break;
            case 'applicant_remove':
                $menu['settings.remove']->setUri('');
                break;
        }
        return $menu;
    }
}
