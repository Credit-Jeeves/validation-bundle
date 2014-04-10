<?php
namespace RentJeeves\TenantBundle\Menu;

use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Enum\OperationType;
use CreditJeeves\DataBundle\Enum\UserIsVerified;
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
                    'route' => 'user_report'
                )
            );
        }

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
            case 'core_report_get_d2c':
            case 'user_report':
                $menu['tabs.report']->setAttribute('class', 'active');
                break;
            case 'user_password':
            case 'user_contact':
            case 'user_email':
            case 'user_remove':
            case 'user_addresses':
            case 'user_address_add_edit':
            case 'user_address_delete':
//                $menu['tabs.settings']->setAttribute('class', 'active');
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
