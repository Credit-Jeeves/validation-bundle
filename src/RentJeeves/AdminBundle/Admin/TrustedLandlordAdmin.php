<?php

namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class TrustedLandlordAdmin extends Admin
{
    const TYPE = 'trusted_landlord';

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_' . self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/' . self::TYPE;
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
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('status');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('checkMailingAddress', null, ['label' => 'Address'])
            ->add('full_name', null, ['label' => 'Full Name'])
            ->add('company_name', null, ['label' => 'Company'])
            ->add('phone')
            ->add('status')
            ->add('jiraMapping.jiraKey', null, ['label' => 'Jira Key']);
    }
}
