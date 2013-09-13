<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class PropertyAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_group_properties';
    }
    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/rj/group/properties/';
    }
    
    
//     /**
//      * {@inheritdoc}
//      */
//     public function createQuery($context = 'list')
//     {
//         $nUserId = $this->getRequest()->get('user_id', $this->request->getSession()->get('user_id', null));
//         $nContractId = $this->getRequest()->get('contract_id', $this->request->getSession()->get('contract_id', null));
//         $query = parent::createQuery($context);
//         $alias = $query->getRootAlias();
//         if (!empty($nUserId)) {
//             $this->request->getSession()->set('user_id', $nUserId);
//             $query->andWhere($alias.'.cj_applicant_id = :user_id');
//             $query->setParameter('user_id', $nUserId);
//         }
//         if (!empty($nContractId)) {
//             $contract =  $this->getModelManager()->find('RjDataBundle:Contract', $nContractId);
//             $this->request->getSession()->set('contract_id', $nContractId);
//             $query->innerJoin($alias.'.operations', $alias.'_o');
//             $query->andWhere($alias.'_o.contract = :contract');
//             $query->setParameter('contract', $contract);
//         }
//         return $query;
//     }

//     public function configureRoutes(RouteCollection $collection)
//     {
//         $collection->remove('delete');
//         $collection->remove('create');
//     }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name');
    }

//     public function configureDatagridFilters(DatagridMapper $datagridMapper)
//     {
//         $datagridMapper
//             ->add('user.email')
//             ->add('type')
//             ->add('amount')
//             ->add('status')
//             ->add(
//                 'created_at',
//                 'doctrine_orm_date'
//             )
//             ->add(
//                 'updated_at',
//                 'doctrine_orm_date'
//             );
//     }

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
                    'Payments List',
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
}
