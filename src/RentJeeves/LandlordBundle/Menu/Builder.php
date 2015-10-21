<?php
namespace RentJeeves\LandlordBundle\Menu;

use Knp\Menu\FactoryInterface;
use RentJeeves\LandlordBundle\Menu\LandlordPermission as Permission;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $sRoute = $this->container->get('request')->get('_route');
        $menu = $factory->createItem('root');
        /**
         * @var $permission Permission
         */
        $permission = $this->container->get('landlord.permission');
        $menu->addChild(
            'tabs.dashboard',
            array(
                'route' => 'landlord_homepage'
            )
        );
        if ($permission->hasAccessToPropertiesTab()) {
            $menu->addChild(
                'tabs.properties',
                array(
                    'route' => 'landlord_properties'
                )
            );
        }
        $menu->addChild(
            'tabs.tenants',
            array(
                'route' => 'landlord_tenants'
            )
        );

        if ($permission->hasAccessToAccountingTab()) {
            if ($permission->hasAccessToImport()) {
                $menu->addChild(
                    'tab.accounting',
                    [
                        'route' => 'accounting_import_file'
                    ]
                );
            } else {
                $menu->addChild(
                    'tab.accounting',
                    [
                        'route' => 'accounting_export'
                    ]
                );
            }
        }

        switch ($sRoute) {
            case 'landlord_homepage':
                $menu['tabs.dashboard']->setAttribute('class', 'active');
                break;
            case 'landlord_properties':
                $menu['tabs.properties']->setAttribute('class', 'active');
                break;
            case 'landlord_tenants_filter':
            case 'landlord_tenants':
                $menu['tabs.tenants']->setAttribute('class', 'active');
                break;
            case 'accounting_import':
            case 'accounting_match_file':
            case 'accounting_import_file':
            case 'import_summary_report':
            case 'accounting_export':
            case 'accounting_deposit':
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

        /**
         * @var Permission $permission
         */
        $permission = $this->container->get('landlord.permission');

        if ($permission->hasAccessToImport()) {
            $menu->addChild(
                'accounting.menu.import',
                [
                    'route' => 'accounting_import_file'
                ]
            );
        }
        if ($permission->hasAccessToExport()) {
            $menu->addChild(
                'accounting.menu.export',
                [
                    'route' => 'accounting_export'
                ]
            );
        }
        $menu->addChild(
            'accounting.menu.batched_deposits',
            [
                'route' => 'accounting_deposit'
            ]
        );

        $route = $this->container->get('request')->get('_route');
        switch ($route) {
            case 'accounting_match_file':
            case 'accounting_import':
            case 'accounting_import_file':
                $menu['accounting.menu.import']->setUri('');
                break;
            case 'accounting_export':
                $menu['accounting.menu.export']->setUri('');
                break;
            case 'accounting_deposit':
                $menu['accounting.menu.batched_deposits']->setUri('');
                break;
        }

        return $menu;
    }
}
