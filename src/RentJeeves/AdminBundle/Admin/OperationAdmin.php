<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class OperationAdmin extends Admin
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
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();

        $id = $this->getRequest()->get('contract_id', null);
        if (!empty($id)) {
            $query->andWhere($alias.'.contract = :contract_id');
            $query->setParameter('contract_id', $id);
        }

        return $query;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('createdAt', 'date')
            ->add('type')
            ->add('amount', 'money')
            ->add('paidFor', 'date')
            ->add('contract', null, array('route' => array('name' => 'show')))
            ->add('order', null, array('route' => array('name' => 'show')))
            ->add('order.created_at')
            ->add('order.type')
            ->add('order.status')
            ->add('order.sum')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                    )
                )
            );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('createdAt', 'doctrine_orm_date')
            ->add('type')
            ->add('amount')
            ->add('paidFor', 'doctrine_orm_date')
            ->add('contract.id')
            ->add('order.id')
            ->add('order.created_at', 'doctrine_orm_date');
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('paidFor');
        ;
    }

    protected function configureShowFields(ShowMapper $formMapper)
    {
        $formMapper
            ->add('createdAt', 'date')
            ->add('type')
            ->add('amount', 'money')
            ->add('paidFor', 'date')
            ->add('days_late')
            ->add('contract', null, array('route' => array('name' => 'show')))
            ->add('order', null, array('route' => array('name' => 'show')));
    }
}
