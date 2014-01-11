<?php

namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Admin\Admin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

class JobAdmin extends Admin
{
    protected $baseRouteName = 'job';
    protected $baseRoutePattern = 'job';


    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', 'string', ['template' => 'AdminBundle:CRUD:job_id.html.twig'])
            ->add('state', 'string', ['template' => 'AdminBundle:CRUD:job_state.html.twig'])
            ->add('command', 'string', ['template' => 'AdminBundle:CRUD:job_command.html.twig'])
            ->add('runtime')
            ->add('createdAt', 'date');
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('state')
            ->add(
                'createdAt',
                'doctrine_orm_date'
            );
    }

//    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
//    {
//        $menu = $this->menuFactory->createItem('root');
//        $menu = $menu->addChild(
//            $this->trans(
//                $this->getLabelTranslatorStrategy()->getLabel(
//                    'Jobs',
//                    'breadcrumb',
//                    'link'
//                ),
//                array(),
//                'SonataAdminBundle'
//            ),
//            array(
//                'uri' => $this->routeGenerator->generate('sonata_admin_dashboard')
//            )
//        );
//
//        return $this->breadcrumbs[$action] = $menu;
//    }
}
