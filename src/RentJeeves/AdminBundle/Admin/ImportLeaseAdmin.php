<?php

namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ImportLeaseAdmin extends Admin
{
    const TYPE = 'import_lease';

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return DIRECTORY_SEPARATOR.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
        $collection->remove('edit');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('externalAccountId')
            ->add('tenantEmail')
            ->add('firstName')
            ->add('lastName')
            ->add('phone')
            ->add('dateOfBirth')
            ->add('externalResidentId')
            ->add('externalPropertyId')
            ->add('externalBuildingId')
            ->add('externalUnitId')
            ->add('externalLeaseId')
            ->add('residentStatus')
            ->add('paymentAccepted')
            ->add('dueDate')
            ->add('rent')
            ->add('integratedBalance')
            ->add('startAt', 'datetime', ['format' => 'Y-m-d'])
            ->add('finishAt', 'datetime', ['format' => 'Y-m-d'])
            ->add('import.id')
            ->add('userStatus')
            ->add('leaseStatus')
            ->add('errorMessages')
            ->add('processed')
            ->add('createdAt', 'datetime', ['format' => 'Y-m-d H:i:s'])
            ->add('updatedAt', 'datetime', ['format' => 'Y-m-d H:i:s']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('import.id');
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('tenantEmail')
            ->add('fullName', null, ['label' => 'Tenant Name'])
            ->add('externalUnitId')
            ->add('externalLeaseId')
            ->add('residentStatus')
            ->add('userStatus')
            ->add('leaseStatus')
            ->add('errorMessages');
    }
}
