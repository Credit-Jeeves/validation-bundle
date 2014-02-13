<?php
namespace RentJeeves\AdminBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class PaymentAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
//        $alias = $query->getRootAlias();
//        $query->
        return $query;
    }

    public function configureRoutes(RouteCollection $collection)
    {
//        $collection->remove('edit');// https://github.com/sonata-project/SonataDoctrineORMAdminBundle/issues/276
        $collection->remove('delete');
        $collection->remove('create');
        $collection->add('run', $this->getRouterIdParameter().'/run');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('start_date', 'date')
            ->add('type')
            ->add('status')
            ->add('amount', 'money')
            ->add('created_at', 'date')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
//                        'run' => array(
//                            'template' => 'AdminBundle:CRUD:list__payment_run.html.twig'
//                        ),
                        'jobs' => array(
                            'template' => 'AdminBundle:CRUD:list__payment_jobs.html.twig',
                        )
                    )
                )
            );
    }

    public function getFilterParameters()
    {
        $return = parent::getFilterParameters();

        if (!isset($return['startDate']['value']['month']) &&
            !isset($return['startDate']['value']['day']) &&
            !isset($return['startDate']['value']['year'])
        ) {
            $return['startDate'] = array(
                'value' => array(
                    'month' => date('n'),
                    'day' => date('j'),
                    'year' => date('Y'),
                ),
            );
        }

        if (!isset($return['status']['value'])) {
            $return['status']['type'] = '3';
            $return['status']['value'] = PaymentStatus::ACTIVE;
        }


        if (!isset($return['type']['value'])) {
            $return['type']['type'] = '2';
            $return['type']['value'] = PaymentType::IMMEDIATE;
        }

        return $return;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'startDate',
                'doctrine_orm_callback',
                array(
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        if (empty($value['value'])) {
                            return false;
                        }
                        /** @var QueryBuilder $queryBuilder */
                        $alias = $queryBuilder->getRootAliases()[0];
                        $queryBuilder->andWhere($alias . '.dueDate = :due_date');

                        $queryBuilder->andWhere(
                            sprintf(
                                "STR_TO_DATE(" .
                                "CONCAT_WS('-', %s.startYear, %s.startMonth, %s.dueDate)," .
                                "'%%Y-%%c-%%e'" .
                                ") <= :start_date",
                                $alias,
                                $alias,
                                $alias
                            )
                        );
                        $queryBuilder->setParameter('due_date', $value['value']->format('d'));
                        $queryBuilder->setParameter('start_date', $value['value']);

                        return true;
                    },
                    'field_type' => 'date'
                )
            )
            ->add('status')
            ->add('type')
            ->add('id')
            ->add('amount')
            ->add(
                'created_at',
                'doctrine_orm_date'
            );
    }

    protected function configureShowFields(ShowMapper $formMapper)
    {
        $formMapper
            ->add('contract', null, array('route' => array('name' => 'show')))
            ->add('paymentAccount', null, array('route' => array('name' => 'show')))
            ->add('type')
            ->add('status')
            ->add('amount')
            ->add('start_date', 'date')
            ->add('endYear')
            ->add('endMonth')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('jobs', null, array('template' => 'AdminBundle:CRUD:list__payment_jobs.html.twig'));

    }

    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        $menu = $this->menuFactory->createItem('root');
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'dashboard',
                    'breadcrumb',
                    'link'
                ),
                array(),
                'SonataAdminBundle'
            ),
            array(
                'uri' => $this->routeGenerator->generate('sonata_admin_dashboard')
            )
        );
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'Payments List',
                    'breadcrumb',
                    'link'
                ),
                array(),
                'SonataAdminBundle'
            ),
            array(
                'uri' => $this->routeGenerator->generate('admin_rentjeeves_data_payment_list')
            )
        );
        return $this->breadcrumbs[$action] = $menu;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        if ($this->isGranted('RUN')) {
            $actions['run'] = array(
                'label'            => $this->trans('Run', array(), 'SonataAdminBundle'),
                'ask_confirmation' => true, // by default always true
            );
        }

        return $actions;
    }
}
