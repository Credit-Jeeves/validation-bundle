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
//         $menu->addChild('settings.password', array('route' => 'applicant_password'));
//         $menu->addChild('settings.contact_information', array('route' => 'user_contact'));
//         $menu->addChild('settings.email', array('route' => 'user_email'));
//         $menu->addChild('settings.remove', array('route' => 'user_remove'));

//         $sRoute = $this->container->get('request')->get('_route');
//         switch ($sRoute) {
//             case 'applicant_password':
//                 $menu['settings.password']->setUri('');
//                 break;
//             case 'user_contact':
//                 $menu['settings.contact_information']->setUri('');
//                 break;
//             case 'user_email':
//                 $menu['settings.email']->setUri('');
//                 break;
//             case 'user_remove':
//                 $menu['settings.remove']->setUri('');
//                 break;
//         }
        return $menu;
    }
}
