<?php
namespace RentJeeves\TenantBundle\Menu;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OperationType;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        /** @var User $user */
        $user = $this->container->get('core.session.tenant')->getUser();
        $isCompleteOrder = $user->getLastCompleteOperation(OperationType::REPORT);
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
        if ($isCompleteOrder) {
            $menu->addChild(
                'tabs.report',
                array(
                    'route' => 'tenant_report'
                )
            );
        }
        $menu->addChild(
            'tabs.settings',
            array(
                'route' => 'tenant_password'
            )
        );
        switch ($sRoute) {
            case 'tenant_homepage':
            case 'tenant_payment_history':
            case 'property_add':
            case 'property_add_id':
            case 'tenant_invite_landlord':
            case 'tenant_payment_sources':
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
        $sRoute = $this->container->get('request')->get('_route');
        $menu = $factory->createItem('root');
        $menu->addChild(
            'settings.password',
            array(
                'route' => 'tenant_password'
            )
        );
        $menu->addChild(
            'settings.contact_information',
            array(
                'route' => 'tenant_contact'
            )
        );
//        $menu->addChild('settings.email', array('route' => 'tenant_email'));
//        $menu->addChild('settings.remove', array('route' => 'tenant_remove'));

       
        switch ($sRoute) {
            case 'tenant_password':
                $menu['settings.password']->setUri('');
                break;
            case 'tenant_contact':
                $menu['settings.contact_information']->setUri('');
                break;
//             case 'tenant_email':
//                 $menu['settings.email']->setUri('');
//                 break;
//             case 'tenant_remove':
//                 $menu['settings.remove']->setUri('');
//                 break;
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
            'rent.history',
            array(
                'route' => 'tenant_payment_history'
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
            case 'tenant_payment_history':
                $menu['rent.history']->setUri('');
                break;
            case 'tenant_payment_sources':
                $menu['rent.sources']->setUri('');
                break;
        }
        return $menu;
    }
}
