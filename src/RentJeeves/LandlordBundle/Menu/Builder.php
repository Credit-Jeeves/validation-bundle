<?php
namespace RentJeeves\LandlordBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $sRoute = $this->container->get('request')->get('_route');
        $menu = $factory->createItem('root');
        $menu->addChild(
            'tabs.dashboard',
            array(
                'route' => 'landlord_homepage'
            )
        );
        $menu->addChild(
            'tabs.properties',
            array(
                'route' => 'landlord_properties'
            )
        );
        $menu->addChild(
            'tabs.tenants',
            array(
                'route' => 'landlord_tenants'
            )
        );
//         $menu->addChild(
//             'tabs.settings',
//             array(
//                 'route' => 'landlord_settings'
//             )
//         );
        switch ($sRoute) {
            case 'landlord_homepage':
                $menu['tabs.dashboard']->setAttribute('class', 'active');
                break;
            case 'landlord_property_new':
            case 'landlord_properties':
                $menu['tabs.properties']->setAttribute('class', 'active');
                break;
            case 'landlord_tenants':
                $menu['tabs.tenants']->setAttribute('class', 'active');
                break;
            default:
                break;
        }
        
        return $menu;
    }

    public function settingsMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('account.information', array('route' => 'landlord_edit_profile'));
        $menu->addChild('settings.password', array('route' => 'landlord_password'));


        $sRoute = $this->container->get('request')->get('_route');
        switch ($sRoute) {
            case 'landlord_edit_profile':
                $menu['account.information']->setUri('');
                break;
            case 'landlord_password':
                $menu['settings.password']->setUri('');
                break;
        }
        return $menu;
    }
}
