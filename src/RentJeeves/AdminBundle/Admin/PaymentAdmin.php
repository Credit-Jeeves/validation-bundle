<?php
namespace RentJeeves\AdminBundle\Admin;

use Doctrine\ORM\QueryBuilder;
use RentJeeves\CoreBundle\Traits\DateCommon;
use RentJeeves\DataBundle\Entity\Payment;
use RentJeeves\DataBundle\Entity\PaymentRepository;
use RentJeeves\DataBundle\Enum\PaymentCloseReason;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PaymentAdmin extends Admin
{
    use DateCommon;

    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
        $collection->add('run', $this->getRouterIdParameter().'/run');
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $orderId = $this->getRequest()->get('order_id', $this->getRequest()->get('order_id', null));
        $tenantId = $this->getRequest()->get('tenant_id', null);

        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();

        if (!empty($orderId)) {
            $query->innerJoin($alias.'.orders', $alias.'_o');
            $query->andWhere($alias.'_o.id = :order_id');
            $query->setParameter('order_id', $orderId);
        }

        if (!empty($tenantId)) {
            $query->innerJoin($alias.'.contract', $alias.'_c');
            $query->andWhere($alias.'_c.tenant = :tenant');
            $query->setParameter('tenant', $tenantId);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
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
            ->add('other', 'money')
            ->add('created_at', 'date')
            ->add(
                '_action',
                'actions',
                [
                    'actions' => [
                        'edit' => [],
                        'jobs' => ['template' => 'AdminBundle:CRUD:list__payment_jobs.html.twig'],
                        'orders' => ['template' => 'AdminBundle:CRUD:list__payment_orders.html.twig'],
                        'paymentHistory' => [
                            'template' => 'AdminBundle:CRUD:list__payment_history.html.twig',
                        ],
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('dueDate')
            ->add('startMonth')
            ->add('startYear')
            ->add('status', 'choice', ['choices' => PaymentStatus::cachedTitles()])
            ->add('amount');

        $self = $this;
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $form = $event->getForm();
                /** @var Payment $payment */
                $payment = $form->getData();
                $closeDetails = $payment->getCloseDetails();
                if (PaymentStatus::CLOSE === $payment->getStatus() && empty($closeDetails)) {
                    $payment->setClosed($self, PaymentCloseReason::CLOSED_BY_ADMIN);
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
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
                        $queryBuilder->andWhere(
                            PaymentRepository::getStartDateDQLString($alias) . ' <= :start_date'
                        );
                        $queryBuilder->setParameter('start_date', $value['value']);

                        $queryBuilder->andWhere($alias . '.dueDate IN (:due_date)');
                        $queryBuilder->setParameter(
                            'due_date',
                            $this->getDueDays(0, new DateTime($value['value']->format('Y-m-d')))
                        );

                        return true;
                    },
                    'field_type' => 'date'
                )
            )
            ->add('status', 'doctrine_orm_choice', [], 'choice', ['choices' => PaymentStatus::cachedTitles()])
            ->add('type')
            ->add('id')
            ->add('amount')
            ->add('created_at', 'doctrine_orm_date');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $formMapper)
    {
        $formMapper
            ->add('contract', null, array('route' => array('name' => 'show')))
            ->add('paymentAccount', null, array('route' => array('name' => 'show')))
            ->add('type')
            ->add('status')
            ->add('amount', 'money', array('label' => 'Rent'))
            ->add('other', 'money', array('label' => 'Other'))
            ->add('dueDate', null, array('label' => 'Date'))
            ->add('startMonth')
            ->add('startYear')
            ->add('endYear')
            ->add('endMonth')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('jobs', null, array('template' => 'AdminBundle:CRUD:list__payment_jobs.html.twig'));

    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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
