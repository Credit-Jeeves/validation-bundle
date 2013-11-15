<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class UnitAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        /**
         * This executes everywhere in the admin and disables softdelete for everything, if you need something cleverer this should be rethought.
         */
        $filters = $this->getModelManager()->getEntityManager($this->getClass())->getFilters();

        if (array_key_exists('softdeleteable', $filters->getEnabledFilters())) {
            $filters->disable('softdeleteable');
        }

    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('create');
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nLandlordId = $this->getRequest()->get('landlord_id', $this->request->getSession()->get('landlord_id', null));
        $nGroupId = $this->getRequest()->get('group_id', $this->request->getSession()->get('group_id', null));
        $nPropertyId = $this->getRequest()->get('property_id', $this->request->getSession()->get('property_id', null));

        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        $query->innerJoin($alias.'.property', $alias.'_p');
        $query->innerJoin($alias.'_p.property_groups', $alias.'_g');
        if (!empty($nPropertyId)) {
            $this->request->getSession()->set('property_id', $nPropertyId);
            $property = $this->getModelManager()->find('RjDataBundle:Property', $nPropertyId);
            $query->andWhere($alias.'.property = :property');
            $query->setParameter('property', $property);
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

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('rent')
            ->add('beds')
            ->add('deletedAt')
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
            ->add('name')
            ->add('rent');
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Profile')
                ->add('name')
                ->add('rent')
                ->add('beds')
            ->end();
    }

    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        $nLandlordId = $this->getRequest()->get('landlord_id', $this->request->getSession()->get('landlord_id', null));
        $nGroupId = $this->getRequest()->get('group_id', $this->request->getSession()->get('group_id', null));
        $nPropertyId = $this->getRequest()->get('property_id', $this->request->getSession()->get('property_id', null));

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
        if ('list' == $action & !empty($nPropertyId)) {
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
                    'uri' => $this->routeGenerator->generate('admin_group_properties_list')
                )
            );
        }
        $title = 'Units List';
        if (!empty($nPropertyId)) {
            $property = $this->getModelManager()->find('RjDataBundle:Property', $nPropertyId);
            $title = $property->getFullAddress();
        }
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    $title,
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
