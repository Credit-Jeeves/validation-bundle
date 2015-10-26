<?php
namespace RentJeeves\TenantBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $sRoute = $this->container->get('request')->get('_route');

        $menu = $factory->createItem('root');
        $menu->addChild(
            'tabs.rent',
            array(
                'route' => 'tenant_homepage'
            )
        );

        $menu->addChild(
            'tabs.summary',
            array(
                'route' => 'tenant_summary'
            )
        );

        switch ($sRoute) {
            case 'tenant_homepage':
            case 'property_add':
            case 'property_add_id':
            case 'tenant_invite_landlord':
            case 'tenant_payment_sources':
                $menu['tabs.rent']->setAttribute('class', 'active');
                break;
            case 'core_report_get':
            case 'personal_info_fill_pidkiq':
            case 'pidkiq_questions':
            case 'tenant_summary':
                $menu['tabs.summary']->setAttribute('class', 'active');
                break;
            case 'core_report_get_credittrack':
            case 'user_report':
                $menu['tabs.report']->setAttribute('class', 'active');
                break;
        }

        return $menu;
    }

    public function settingsMenu(FactoryInterface $factory, array $options)
    {
        $sRoute = $this->container->get('request')->get('_route');
        $menu = $factory->createItem('root');
        $menu->addChild(
            'settings.password',
            array(
                'route' => 'user_password'
            )
        );
        $menu->addChild(
            'settings.contact_information',
            array(
                'route' => 'user_contact'
            )
        );
        $menu->addChild('settings.email', array('route' => 'user_email'));
        $menu->addChild('settings.remove', array('route' => 'user_remove'));
        if ($this->container->getParameter('allow_score_track')) {
            $menu->addChild('settings.plans', array('route' => 'user_plans'));
        }
        $menu->addChild('settings.address.head.manage', array('route' => 'user_addresses'));

        switch ($sRoute) {
            case 'user_password':
                $menu['settings.password']->setUri('');
                break;
            case 'user_contact':
                $menu['settings.contact_information']->setUri('');
                break;
            case 'user_email':
                $menu['settings.email']->setUri('');
                break;
            case 'user_remove':
                $menu['settings.remove']->setUri('');
                break;
            case 'user_plans':
                $menu['settings.plans']->setUri('');
                break;
            case 'user_addresses':
                $menu['settings.address.head.manage']->setUri('');
                break;
        }

        return $menu;
    }

    public function rentMenu(FactoryInterface $factory, array $options)
    {
        $sRoute = $this->container->get('request')->get('_route');
        $menu = $factory->createItem('root');
        $menu->addChild(
            'rent.properties',
            array(
                'route' => 'tenant_homepage'
            )
        );
        $menu->addChild(
            'rent.sources',
            array(
                'route' => 'tenant_payment_sources'
            )
        );
        switch ($sRoute) {
            case 'tenant_homepage':
                $menu['rent.properties']->setUri('');
                break;
            case 'tenant_payment_sources':
                $menu['rent.sources']->setUri('');
                break;
        }

        return $menu;
    }
}
