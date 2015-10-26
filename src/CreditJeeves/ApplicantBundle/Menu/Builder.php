<?php
namespace CreditJeeves\ApplicantBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('tabs.action_plan', array('route' => 'applicant_homepage'));
        $menu->addChild('tabs.summary', array('route' => 'applicant_summary'));
        $User = $this->container->get('core.session.applicant')->getUser();
        $isCompleteOrder = $User->isCompleteOrderExist();

        if ($isCompleteOrder) {
            $menu->addChild('tabs.report', array('route' => 'user_report'));
        }
        $menu->addChild('tabs.settings', array('route' => 'user_password'));

        $sRoute = $this->container->get('request')->get('_route');
        switch ($sRoute) {
            case 'applicant_homepage':
                $menu['tabs.action_plan']->setAttribute('class', 'active');
                break;
            case 'core_report_get':
            case 'applicant_summary':
                $menu['tabs.summary']->setAttribute('class', 'active');
                break;
            case 'core_report_get_credittrack':
            case 'user_report':
                $menu['tabs.report']->setAttribute('class', 'active');
                break;
            case 'user_password':
            case 'user_contact':
            case 'user_email':
            case 'user_remove':
            //TODO: unkomment when multiaddresses will be implemented for CJ
//            case 'user_addresses':
//            case 'user_address_add_edit':
//            case 'user_address_delete':
                $menu['tabs.settings']->setAttribute('class', 'active');
                break;
        }

        return $menu;
    }

    public function settingsMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('settings.password', array('route' => 'user_password'));
        $menu->addChild('settings.contact_information', array('route' => 'user_contact'));
        $menu->addChild('settings.email', array('route' => 'user_email'));
        $menu->addChild('settings.remove', array('route' => 'user_remove'));
        //TODO: unkomment when multiaddresses will be implemented for CJ
//        $menu->addChild('settings.address.head.manage', array('route' => 'user_addresses'));

        $sRoute = $this->container->get('request')->get('_route');
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
            //TODO: unkomment when multiaddresses will be implemented for CJ
//            case 'user_addresses':
//                $menu['settings.address.head.manage']->setUri('');
//                break;
        }

        return $menu;
    }
}
