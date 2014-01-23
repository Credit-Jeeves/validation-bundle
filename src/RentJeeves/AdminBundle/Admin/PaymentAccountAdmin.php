<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class PaymentAccountAdmin extends Admin
{

    public function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('edit');// https://github.com/sonata-project/SonataDoctrineORMAdminBundle/issues/276
        $collection->remove('delete');
        $collection->remove('create');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('type')
            ->add('name')
            ->add('token')
            ->add('ccExpiration')
            ->add('createdAt')
        ;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('type')
            ->add('name')
            ->add('token')
            ->add('ccExpiration')
            ->add(
                'createdAt',
                'doctrine_orm_date'
            );
    }

    protected function configureShowFields(ShowMapper $formMapper)
    {
        $formMapper
            ->add('user', null, array('route' => array('name' => 'show')))
            ->add('group', null, array('route' => array('name' => 'show')))
            ->add('address', null, array('route' => array('name' => 'show')))
            ->add('type')
            ->add('name')
            ->add('token')
            ->add('ccExpiration')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('payments', null, array('route' => array('name' => 'show')))
        ;

    }
}
