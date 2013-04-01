<?php
namespace CreditJeeves\ApplicantBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('Action Plan', array('route' => 'applicant_homepage'));
        $menu->addChild('Summary',     array('route' => 'applicant_summary'));
        
        $User = $this->container->get('security.context')->getToken()->getUser();
        $isCompleteOrder = $User->isCompleteOrderExist();
        
        if ($isCompleteOrder) {
            $menu->addChild('Report',      array('route' => 'applicant_report'));
        }
        
        $menu->addChild('Settings',    array('route' => 'applicant_password'));
        
        $sRoute = $this->container->get('request')->get('_route');
        switch ($sRoute) {
            case 'applicant_homepage':
                $menu['Action Plan']->setAttribute('class', 'active');
                break;
            case 'applicant_summary':
                $menu['Summary']->setAttribute('class', 'active');
                break;
            case 'applicant_report':
                $menu['Report']->setAttribute('class', 'active');
                break;
            default:
                $menu['Settings']->setAttribute('class', 'active');
                break;
        }
        
        return $menu;
    }
}