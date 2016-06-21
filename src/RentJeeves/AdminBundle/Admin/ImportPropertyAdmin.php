<?php

namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class ImportPropertyAdmin extends Admin
{
    const TYPE = 'import_property';

    const MAX_LIMIT = 100;

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
        $collection->remove('create');
        $collection->remove('edit');
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->setMaxResults(self::MAX_LIMIT);

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
            ->add('externalPropertyId')
            ->add('externalBuildingId')
            ->add('addressHasUnits')
            ->add('unitName')
            ->add('externalUnitId')
            ->add('address1')
            ->add('city')
            ->add('status')
            ->add('processed')
            ->add(
                '_action',
                'actions',
                ['actions' => ['show' => []]]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('import.id')
            ->add('externalPropertyId')
            ->add('externalBuildingId')
            ->add('addressHasUnits')
            ->add('propertyHasBuildings')
            ->add('unitName')
            ->add('externalUnitId')
            ->add('address1')
            ->add('city')
            ->add('state')
            ->add('zip')
            ->add('status')
            ->add('errorMessages')
            ->add('processed');
    }

    /**
     * {@inheritdoc}
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('import.id')
            ->add('externalPropertyId')
            ->add('externalBuildingId')
            ->add('unitName')
            ->add('address1')
            ->add('externalUnitId')
            ->add('status')
            ->add('processed');
    }
}
