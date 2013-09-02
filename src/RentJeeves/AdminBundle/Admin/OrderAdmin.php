<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class OrderAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
        $nContractId = $this->getRequest()->get('contract_id', $this->request->getSession()->get('contract_id', null));
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        if (!empty($nUserId)) {
            $this->request->getSession()->set('user_id', $nUserId);
            $query->andWhere($alias.'.cj_applicant_id = :user_id');
            $query->setParameter('user_id', $nUserId);
        }
        if (!empty($nContractId)) {
            $contract =  $this->getModelManager()->find('RjDataBundle:Contract', $nContractId);
            
            $this->request->getSession()->set('contract_id', $nContractId);
            $query->innerJoin($alias.'.operations', $alias.'_o');
            $query->andWhere($alias.'_o.contract = :contract');
            $query->setParameter('contract', $contract);
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
            ->add('created_at', 'date')
            ->add('updated_at', 'date')
            ->add('type')
            ->add('status')
            ->add('heartland_transaction_id')
            ->add('operation_type')
            ->add('amount', 'money')
            ->add('user.full_name')
            ->add('user.email');
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('user.email')
            ->add('type')
            ->add('amount')
            ->add('status')
            ->add('created_at', 'doctrine_orm_date')
            ->add('updated_at', 'doctrine_orm_date')
            
        ;
    }

//     public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
//     {
//         echo __METHOD__;
//         return parent::buildBreadcrumbs($action, $menu);
        
//     }
    
}
