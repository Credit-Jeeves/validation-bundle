<?php
namespace RentJeeves\AdminBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use RentJeeves\CoreBundle\Traits\DateCommon;
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
    use DateCommon;

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
            ->add('dueDate')
            ->add('startMonth')
            ->add('startYear')
            ->add('endYear')
            ->add('type')
            ->add('status')
            ->add('amount', 'money')
            ->add('created_at', 'date')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
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
        } else {
            $day = cal_days_in_month(
                CAL_GREGORIAN,
                $return['startDate']['value']['month'],
                $return['startDate']['value']['year']
            );
            $return['startDate']['value']['day'] = $day < $return['startDate']['value']['day'] ?
                $day :
                $return['startDate']['value']['day'];
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

                        $queryBuilder->addSelect(
                            str_replace(
                                '%alias',
                                $alias,
                                "@startDateLastDay := DAY(LAST_DAY(STR_TO_DATE(" .
                                "CONCAT_WS('-', %alias.startYear, %alias.startMonth, '1')," .
                                "'%Y-%c-%e'" .
                                ")))"
                            )
                        );

//                        $queryBuilder->addSelect(
//                            str_replace(
//                                '%alias',
//                                $alias,
//                                "STR_TO_DATE(" .
//                                "CONCAT_WS(
//                                    '-',
//                                    %alias.startYear,
//                                    %alias.startMonth,
//                                    IF(@startDateLastDay < %alias.dueDate, @startDateLastDay, %alias.dueDate),
//                                " .
//                                "'%Y-%c-%e'" .
//                                ") AS startDate"
//                            )
//                        );
//                        $queryBuilder->having("startDate <= :start_date");
//                        $queryBuilder->setParameter('start_date', $value['value']);

                        $queryBuilder->andWhere($alias . '.dueDate IN (:due_date)');
                        $queryBuilder->setParameter('due_date', $this->getDueDays(0, $value['value']));

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
            ->add('dueDate')
            ->add('startMonth')
            ->add('startYear')
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
