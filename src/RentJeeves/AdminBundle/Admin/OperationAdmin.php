<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Enum\OrderStatus;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class OperationAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this->datagridValues['_sort_by']    = 'id';
        $this->datagridValues['_sort_order'] = 'DESC';
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
//        $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
//        $nContractId = $this->getRequest()->get('contract_id', $this->request->getSession()->get('contract_id', null));
        $query = parent::createQuery($context);
//        $alias = $query->getRootAlias();
//        if (!empty($nUserId)) {
//            $this->request->getSession()->set('user_id', $nUserId);
//            $query->andWhere($alias.'.cj_applicant_id = :user_id');
//            $query->setParameter('user_id', $nUserId);
//        }
//        if (!empty($nContractId)) {
//            $contract =  $this->getModelManager()->find('RjDataBundle:Contract', $nContractId);
//            $this->request->getSession()->set('contract_id', $nContractId);
//            $query->innerJoin($alias.'.operations', $alias.'_o');
//            $query->andWhere($alias.'_o.contract = :contract');
//            $query->setParameter('contract', $contract);
//        }
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
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('createdAt', 'date')
            ->add('type')
            ->add('amount', 'money')
            ->add('paidFor', 'date')
            ->add('contract', null, array('route' => array('name' => 'show')))
            ->add('order', null, array('route' => array('name' => 'show')))
            ->add('order.created_at')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                    )
                )
            );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('createdAt', 'doctrine_orm_date')
            ->add('type')
            ->add('amount')
            ->add('paidFor', 'doctrine_orm_date')
            ->add('contract.id')
            ->add('order.id')
            ->add('order.created_at', 'doctrine_orm_date');
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('paidFor');
        ;
    }

    protected function configureShowFields(ShowMapper $formMapper)
    {
        $formMapper
            ->add('createdAt', 'date')
            ->add('type')
            ->add('amount', 'money')
            ->add('paidFor', 'date')
            ->add('contract', null, array('route' => array('name' => 'show')))
            ->add('order', null, array('route' => array('name' => 'show')));
    }
}
