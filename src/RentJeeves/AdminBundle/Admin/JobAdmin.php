<?php

namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Admin\Admin;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Doctrine\ORM\Query\Expr;

class JobAdmin extends Admin
{
    protected $baseRouteName = 'admin_job';
    protected $baseRoutePattern = 'job';

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
            ->add(
                'payment',
                'doctrine_orm_callback',
                array(
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        if (empty($value['value'])) {
                            return false;
                        }
                        $queryBuilder->innerJoin(
                            $alias . '.relatedEntities',
                            're'
                        );
                        $queryBuilder->where('re instance of RentJeeves\DataBundle\Entity\JobRelatedPayment');
                        $queryBuilder->andWhere(
                            're.id in (
                                select jrp.id
                                from RentJeeves\DataBundle\Entity\JobRelatedPayment jrp
                                where jrp.payment = :payment_id
                            )'
                        );
                        $queryBuilder->setParameter('payment_id', (int)$value['value']);

                        return true;
                    },
                    'field_type' => 'integer'
                )
            )
            ->add(
                'order',
                'doctrine_orm_callback',
                array(
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        if (empty($value['value'])) {
                            return false;
                        }
                        $queryBuilder->innerJoin(
                            $alias . '.relatedEntities',
                            're'
                        );
                        $queryBuilder->where('re instance of RentJeeves\DataBundle\Entity\JobRelatedOrder');
                        $queryBuilder->andWhere(
                            're.id in (
                                select jro.id
                                from RentJeeves\DataBundle\Entity\JobRelatedOrder jro
                                where jro.order = :order_id
                            )'
                        );
                        $queryBuilder->setParameter('order_id', (int)$value['value']);

                        return true;
                    },
                    'field_type' => 'integer'
                )
            )
            ->add('id')
            ->add('state')
            ->add(
                'createdAt',
                'doctrine_orm_date'
            );
    }
}
