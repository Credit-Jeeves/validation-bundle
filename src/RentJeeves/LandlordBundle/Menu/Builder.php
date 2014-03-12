<?php
namespace RentJeeves\LandlordBundle\Menu;

use Knp\Menu\FactoryInterface;
use RentJeeves\DataBundle\Entity\Landlord;
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
        /**
         * @var $user Landlord
         */
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user->haveAccessToReports()) {
            $menu->addChild(
                'tab.accounting',
                array(
                    'route' => 'landlord_reports_import'
                )
            );
        }

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
            case 'landlord_reports_review_and_post':
            case 'landlord_reports_match_file':
            case 'landlord_reports_import':
            case 'landlord_reports_export':
                $menu['tab.accounting']->setAttribute('class', 'active');
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
        $menu->addChild('settings.deposit', array('route' => 'settings_payment_accounts'));


        $sRoute = $this->container->get('request')->get('_route');
        switch ($sRoute) {
            case 'landlord_edit_profile':
                $menu['account.information']->setUri('');
                break;
            case 'landlord_password':
                $menu['settings.password']->setUri('');
                break;
            case 'settings_payment_accounts':
                $menu['settings.deposit']->setUri('');
                break;
        }
        return $menu;
    }

    public function accountingMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild(
            'import',
            array(
                'route' => 'landlord_reports_import'
            )
        );
        $menu->addChild(
            'export',
            array(
                'route' => 'landlord_reports_export'
            )
        );

        $route = $this->container->get('request')->get('_route');
        switch ($route) {
            case 'landlord_reports_match_file':
            case 'landlord_reports_review_and_post':
            case 'landlord_reports_import':
                $menu['import']->setUri('');
                break;
            case 'landlord_reports_export':
                $menu['export']->setUri('');
                break;
        }

        return $menu;
    }

}
