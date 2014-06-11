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

class OrderAdmin extends Admin
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
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('created_at', 'date')
            ->add('updated_at', 'date')
            ->add('type')
            ->add('status', 'string', ['template' => 'AdminBundle:CRUD:payments_status_choice.html.twig'])
            ->add('heartland_transaction_ids')
            ->add('operation_type')
            ->add('amount', 'money')
            ->add('group_name', 'string', ['template' => 'AdminBundle:CRUD:payments_group_landlords.html.twig'])
            ->add('user.full_name', 'string', ['template' => 'AdminBundle:CRUD:payments_show_tenant.html.twig'])
            ->add('user.email');
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('user.email')
            ->add('type')
            ->add('sum')
            ->add(
                'transaction_id',
                'doctrine_orm_callback',
                [
                    'callback' => function ($queryBuilder, $alias, $field, $value) {
                        if (empty($value['value'])) {
                            return;
                        }
                        $queryBuilder
                            ->innerJoin($alias.'.heartlands', $alias.'_h')
                            ->where($alias.'_h.transactionId = :id')
                            ->setParameter('id', $value['value']);

                        if ($queryBuilder->getQuery()->getResult()) {
                            return true;
                        }

                        return false;
                    },
                    'field_type' => 'text'
                ]
            )
            ->add('status')
            ->add(
                'created_at',
                'doctrine_orm_date'
            )
            ->add(
                'updated_at',
                'doctrine_orm_date'
            );
    }

    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
        $nContractId = $this->getRequest()->get('contract_id', $this->request->getSession()->get('contract_id', null));
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
        if ('list' == $action & !empty($nUserId)) {
            $menu = $menu->addChild(
                $this->trans(
                    $this->getLabelTranslatorStrategy()->getLabel(
                        'Tenant List',
                        'breadcrumb',
                        'link'
                    ),
                    array(),
                    'SonataAdminBundle'
                ),
                array(
                    'uri' => $this->routeGenerator->generate('admin_tenant_list')
                )
            );
        }
        if ('list' == $action & !empty($nContractId)) {
            $menu = $menu->addChild(
                $this->trans(
                    $this->getLabelTranslatorStrategy()->getLabel(
                        'Contracts List',
                        'breadcrumb',
                        'link'
                    ),
                    array(),
                    'SonataAdminBundle'
                ),
                array(
                    'uri' => $this->routeGenerator->generate('admin_rentjeeves_data_contract_list')
                )
            );
        }
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'Orders List',
                    'breadcrumb',
                    'link'
                ),
                array(),
                'SonataAdminBundle'
            ),
            array(
                'uri' => $this->routeGenerator->generate('admin_creditjeeves_data_order_list')
            )
        );
        return $this->breadcrumbs[$action] = $menu;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add(
                'status',
                'choice',
                array('choices' => OrderStatus::getManualAvailableToSet($this->getSubject()->getStatus()))
            );
        ;
    }

    protected function configureShowFields(ShowMapper $formMapper)
    {
        $formMapper
            ->add('user', null, array('route' => array('name' => 'show')))
            ->add('status')
            ->add('type')
            ->add('amount')
            ->add('days_late')
            ->add('created_at')
            ->add('updated_at')
            ->add('authorizes', null, array('route' => array('name' => 'show')))
            ->add('heartlands', null, array('route' => array('name' => 'show')))
            // FIXME these do not work correctly, because need create Operation list
            ->add('operations', null, array('route' => array('name' => 'show')))
            ->add('jobs', null, array('route' => array('name' => 'show')));
    }
}
