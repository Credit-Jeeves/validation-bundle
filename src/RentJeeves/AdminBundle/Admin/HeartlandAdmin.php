<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Admin\Admin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Show\ShowMapper;

class HeartlandAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this->datagridValues['_sort_by']    = 'id';
        $this->datagridValues['_sort_order'] = 'DESC';
    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
//        $collection->remove('edit');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id',  null, array('route' => array('name' => 'show')))
            ->add('order_id',  null, array('route' => array('name' => 'show')))
            ->add('messages')
            ->add('isSuccessful')
            ->add('amount', 'money')
            ->add('transaction_id')
            ->add('merchant_name')
            ->add('createdAt', 'date')
        ;
    }

    protected function configureShowFields(ShowMapper $formMapper)
    {
        $formMapper
            ->add('order', null, array('route' => array('name' => 'show')))
            ->add('messages')
            ->add('isSuccessful')
            ->add('amount', 'money')
            ->add('transactionId')
            ->add('merchantName')
            ->add('createdAt')
        ;
    }
}
