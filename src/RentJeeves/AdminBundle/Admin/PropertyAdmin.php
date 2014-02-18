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
        return '/rj/group/properties';
    }
    
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nGroupId = $this->getRequest()->get('group_id', $this->request->getSession()->get('group_id', null));
        $nLandlordId = $this->getRequest()->get('landlord_id', $this->request->getSession()->get('landlord_id', null));
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        $query->innerJoin($alias.'.property_groups', $alias.'_g');
        if (!empty($nGroupId)) {
            $this->request->getSession()->set('group_id', $nGroupId);
            $query->andWhere($alias.'_g.id = :group_id');
            $query->setParameter('group_id', $nGroupId);
        }
        if (!empty($nLandlordId)) {
            $this->request->getSession()->set('landlord_id', $nLandlordId);
            $landlord = $this->getModelManager()->find('RjDataBundle:Landlord', $nLandlordId);
            $holding = $landlord->getHolding();
            if ($isSuper = $landlord->getIsSuperAdmin()) {
                 $query->andWhere($alias.'_g.holding = :holding');
                 $query->setParameter('holding', $holding);
            } else {
                $query->innerJoin($alias.'_g.group_agents', $alias.'_l');
                $query->andWhere($alias.'_l.id = :landlord_id');
                $query->setParameter('landlord_id', $nLandlordId);
            }
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
            ->add('number')
            ->add('street')
            ->add('zip')
            ->add('city')
            ->add('area')
            ->add('country')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'units' => array(
                            'template' => 'AdminBundle:CRUD:list__property_units.html.twig'
                        ),
                     )
                )
            );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('number')
            ->add('street')
            ->add('city')
            ->add('zip')
            ->add('area')
            ->add('country');
    }

    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        $nLandlordId = $this->getRequest()->get('landlord_id', $this->request->getSession()->get('landlord_id', null));
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
        if ('list' == $action & !empty($nLandlordId)) {
            $menu = $menu->addChild(
                $this->trans(
                    $this->getLabelTranslatorStrategy()->getLabel(
                        'Landlord List',
                        'breadcrumb',
                        'link'
                    ),
                    array(),
                    'SonataAdminBundle'
                ),
                array(
                    'uri' => $this->routeGenerator->generate('admin_landlord_list')
                )
            );
        }
        if ('list' == $action & !empty($nGroupId)) {
            $menu = $menu->addChild(
                $this->trans(
                    $this->getLabelTranslatorStrategy()->getLabel(
                        'Group List',
                        'breadcrumb',
                        'link'
                    ),
                    array(),
                    'SonataAdminBundle'
                ),
                array(
                    'uri' => $this->routeGenerator->generate('admin_rj_group_list')
                )
            );
        }
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'Property List',
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

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('country')
            ->add('area')
            ->add('city')
            ->add('district')
            ->add('street')
            ->add('number')
            ->add('zip')
            ->add('google_reference')
            ->add('jb')
            ->add('kb');
    }

    protected function configureShowField(ShowMapper $showMapper)
    {
        parent::configureShowField($showMapper);
        $showMapper
            ->add('country')
            ->add('area')
            ->add('city')
            ->add('district')
            ->add('street')
            ->add('number')
            ->add('zip')
            ->add('google_reference')
            ->add('jb')
            ->add('kb')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('units', null, array('route' => array('name' => 'show')))
            ->add('invite', null, array('route' => array('name' => 'show')))
            ->add('property_groups', null, array('route' => array('name' => 'show')))
            ->add('contracts', null, array('route' => array('name' => 'show')));
    }
}
