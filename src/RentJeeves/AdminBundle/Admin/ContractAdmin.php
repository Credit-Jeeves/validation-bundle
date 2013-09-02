<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ContractAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        if (!empty($nUserId)) {
            $this->request->getSession()->set('user_id', $nUserId);
            $tenant = $this->getModelManager()->find('RjDataBundle:Tenant', $nUserId);
            $query->andWhere($alias.'.tenant = :tenant');
            $query->setParameter('tenant', $tenant);
        }
        return $query;
    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('holding.name')
            ->add('group.name')
            ->add('unit.name')
            ->add('status')
            ->add('rent')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'payments' => array(
                            'template' => 'AdminBundle:CRUD:list__contract_orders.html.twig'
                        )
                    )
                )
            );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('status')
            ->add('holding.name')
            ->add('group.name')
            ->add('unit.name')
            ->add('rent')
//             ->add('created_at', 'doctrine_orm_date')
//             ->add('updated_at', 'doctrine_orm_date')
            
        ;
    }
}
