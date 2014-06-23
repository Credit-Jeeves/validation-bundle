<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use RentJeeves\AdminBundle\Admin\TenantAdmin;

class ContractAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $this->getConfigurationPool()->getContainer()->get('soft.deleteable.control')->disable();
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
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('holding.name')
            ->add('group.name')
            ->add('unit.name')
            ->add('status')
            ->add('rent')
            ->add('paidTo', null, array('label' => 'Paid Through'))
            ->add('disputeCode', 'string', ['template' => 'AdminBundle:CRUD:contract_dispute_code_choice.html.twig'])
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
            ->add('rent');
    }

    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
        $nGroupId = $this->getRequest()->get('group_id', $this->request->getSession()->get('group_id', null));
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
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'Contract List',
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
        return $this->breadcrumbs[$action] = $menu;
    }

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('tenant', null, array('route' => array('name' => 'show')))
            ->add('holding', null, array('route' => array('name' => 'show')))
            ->add('group', null, array('route' => array('name' => 'show')))
            ->add('property', null, array('route' => array('name' => 'show')))
            ->add('unit', null, array('route' => array('name' => 'show')))
            ->add('search')
            ->add('status')
            ->add('paidTo')
            ->add('reportToExperian')
            ->add('experianStartAt')
            ->add('reportToTransUnion')
            ->add('transUnionStartAt')
            ->add('startAt')
            ->add('finishAt')
            ->add('uncollectedBalance')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('operations.order', null, array('route' => array('name' => 'show')));
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('tenant')
            ->add('holding')
            ->add('group')
            ->add('property')
            ->add('unit')
            ->add('search')
            ->add('status')
            ->add('paidTo')
            ->add('reportToExperian')
            ->add('experianStartAt')
            ->add('reportToTransUnion')
            ->add('transUnionStartAt')
            ->add('startAt')
            ->add('finishAt');
    }
}
