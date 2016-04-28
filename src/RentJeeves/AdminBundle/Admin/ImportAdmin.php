<?php

namespace RentJeeves\AdminBundle\Admin;

use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class ImportAdmin extends Admin
{
    const TYPE = 'import';

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this->datagridValues['_sort_by'] = 'id';
        $this->datagridValues['_sort_order'] = 'DESC';
    }

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
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('group.name')
            ->add('user.email')
            ->add('importType')
            ->add('status')
            ->add('count', null, ['template' => 'AdminBundle:CRUD:list__import_count_properties.html.twig'])
            ->add('createdAt', 'datetime', ['format' => 'Y-m-d H:i:s'])
            ->add('finishedAt', 'datetime', ['format' => 'Y-m-d H:i:s'])
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'import_properties' => [
                            'template' => 'AdminBundle:CRUD:list__import_properties.html.twig'
                        ],
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('group.name')
            ->add('user.email')
            ->add('importType')
            ->add('status')
            ->add('createdAt', 'doctrine_orm_date')
            ->add('finishedAt', 'doctrine_orm_date');
    }

    /**
     * Used in template to display count of properties in one import.
     *
     * @param $importId
     * @return int
     */
    public function getAdminCountImportProperties($importId)
    {
        /** @var EntityManager $em */
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine.orm.default_entity_manager');

        return $em->getRepository('RjDataBundle:ImportProperty')
            ->createQueryBuilder('ip')
            ->select('count(distinct ip.id)')
            ->where('ip.import = :import_id')
            ->setParameter('import_id', $importId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
