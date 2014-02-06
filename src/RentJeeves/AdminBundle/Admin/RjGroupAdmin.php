<?php
namespace RentJeeves\AdminBundle\Admin;

use RentJeeves\AdminBundle\Form\GroupSettings;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use CreditJeeves\DataBundle\Enum\GroupType;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class RjGroupAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'group';

    protected $formOptions = array(
            'validation_groups' => 'holding'
    );

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_rj_'.self::TYPE;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add(
            'terminal',
            $this->getRouterIdParameter().'/terminal'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nHoldingId = $this->getRequest()->get('holding_id', $this->request->getSession()->get('holding_id', null));
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        $query->add('where', $query->expr()->in($alias.'.type', array(GroupType::RENT)));
        if (!empty($nHoldingId)) {
            $this->request->getSession()->set('holding_id', $nHoldingId);
            $query->andWhere($alias.'.holding_id = :holding_id');
            $query->setParameter('holding_id', $nHoldingId);
        }
        
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $object->setType(GroupType::RENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/rj/'.self::TYPE;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('holding')
            ->add('affiliate')
            ->add('groupPhones')
            ->add('count_properties')
            ->add(
                'deposit_account',
                'sonata_type_model',
                array(
                    'empty_value' => 'None',
                    'required' => false,
                    'label' => 'Merchant status'
                )
            )
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                        'tenants' => array(
                            'template' => 'AdminBundle:CRUD:list__action_tenants.html.twig'
                        ),
                        'landlords' => array(
                            'template' => 'AdminBundle:CRUD:list__action_landlords.html.twig'
                        ),
                        'properties' => array(
                             'template' => 'AdminBundle:CRUD:list__action_properties.html.twig'
                        )
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Details')
                ->add(
                    'holding',
                    'sonata_type_model'
                )
                ->add(
                    'affiliate',
                    'sonata_type_model',
                    array(
                        'empty_value' => 'None',
                        'required' => false
                    )
                )
                ->add('name')
                ->add('merchant_name', 'text')
            ->end()
            ->with('Group Phones')
                ->add(
                    'groupPhones',
                    'sonata_type_collection',
                    array(
                        // Prevents the "Delete" option from being displayed
                        'type_options' => array(
                            'delete' => false
                        )
                    ),
                    array(
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
                    )
                )
            ->end()
            ->with('Virtual terminal')
                ->add(
                    'amount',
                    'text',
                    array(
                        'mapped' => false,
                        'required' => false,
                        'attr' => array(
                            'class' => 'terminal_amount'
                        )
                    )
                )
                ->add(
                    'custom',
                    'text',
                    array(
                        'mapped' => false,
                        'required' => false,
                        'label' => 'ID4 field',
                        'attr' => array(
                            'class' => 'terminal_custom',
                            'pattern' => '^.{1,14}$'
                        )
                    )
                )
            ->end()
            ->with('Settings')
                ->add(
                    'groupSettings',
                    new GroupSettings(),
                    array(
                    ),
                    array(
                        'edit'      => 'inline',
                        'inline'    => 'table',
                        'sortable'  => 'position',
                    )
                )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('deposit_account.status', null, array('label' => 'Merchant status'));
    }

    
    public function buildBreadcrumbs($action, MenuItemInterface $menu = null)
    {
        $nHoldingId = $this->getRequest()->get('holding_id', $this->request->getSession()->get('holding_id', null));
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
        if ('list' == $action & !empty($nHoldingId)) {
            $menu = $menu->addChild(
                $this->trans(
                    $this->getLabelTranslatorStrategy()->getLabel(
                        'Landlords List',
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
        $menu = $menu->addChild(
            $this->trans(
                $this->getLabelTranslatorStrategy()->getLabel(
                    'RentTrack Groups List',
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
        return $this->breadcrumbs[$action] = $menu;
    }
}
