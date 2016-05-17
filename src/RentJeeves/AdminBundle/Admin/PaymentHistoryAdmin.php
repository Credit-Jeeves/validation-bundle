<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class PaymentHistoryAdmin extends Admin
{

    const TYPE = 'payment_history';

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
        return DIRECTORY_SEPARATOR . self::TYPE;
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
    public function createQuery($context = 'list')
    {
        $this->getConfigurationPool()->getContainer()->get('soft.deleteable.control')->disable();
        $paymentId = $this->getRequest()->get('payment_id', null);

        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();

        if ($paymentId) {
            $query->andWhere($alias.'.objectId = :payment_id');
            $query->setParameter('payment_id', $paymentId);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, ['route' => ['name' => 'show']])
            ->add('object.contract', null, ['route' => ['name' => 'show'], 'label' => 'Contract'])
            ->add(
                'object.paymentAccount',
                null,
                [
                    'label' => 'Payment Account',
                    'template' => 'AdminBundle:CRUD:list__payment_account.html.twig',
                ]
            )
            ->add(
                'object',
                null,
                [
                    'label' => 'Deposit Account',
                    'template' => 'AdminBundle:CRUD:list__deposit_account.html.twig',
                ]
            )
            ->add('dueDate')
            ->add('startMonth')
            ->add('startYear')
            ->add('endYear')
            ->add('type')
            ->add('status')
            ->add('amount', 'money')
            ->add('total', 'money')
            ->add('loggedAt', 'date')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('object.id', null, ['label' => 'Payment'])
            ->add('status')
            ->add('type');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('object.contract', null, ['route' => ['name' => 'show'], 'label' => 'Contract'])
            ->add('object.paymentAccount', null, ['route' => ['name' => 'show'], 'label' => 'Payment Account'])
            ->add(
                'object',
                null,
                [
                    'label' => 'Deposit Account',
                    'template' => 'AdminBundle:CRUD:show__deposit_account.html.twig',
                ]
            )
            ->add('type')
            ->add('status')
            ->add('amount', 'money', array('label' => 'Rent'))
            ->add('total', 'money')
            ->add('other', 'money', array('label' => 'Other'))
            ->add('dueDate', null, array('label' => 'Date'))
            ->add('startMonth')
            ->add('startYear')
            ->add('endYear')
            ->add('object.createdAt', 'date', ['label' => 'Created At'])
            ->add('object.updatedAt', 'date', ['label' => 'Updated At'])
            ->add('loggedAt', 'date');
    }
}
