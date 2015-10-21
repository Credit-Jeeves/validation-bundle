<?php

namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class OutboundTransactionAdmin extends Admin
{
    protected $baseRouteName = 'admin_outbound_transaction';
    protected $baseRoutePattern = 'outbound_transaction';

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null)
            ->add('transactionId')
            ->add('batchId')
            ->add('amount', 'money')
            ->add('type')
            ->add('status')
            ->add('message')
            ->add('reversalDescription')
            ->add('depositDate', 'date')
            ->add('batchCloseDate', 'date')
            ->add('createdAt', 'date');
    }

    /**
     * {@inheritdoc}
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('batchId')
            ->add('transactionId')
            ->add('depositDate', 'doctrine_orm_date')
            ->add('order.id')
            ->add('createdAt', 'doctrine_orm_date');
    }
}
