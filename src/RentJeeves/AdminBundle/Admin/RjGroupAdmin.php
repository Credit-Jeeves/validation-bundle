<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use CreditJeeves\DataBundle\Enum\GroupType;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class RjGroupAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'group';

    protected $formOptions = [
        'validation_groups' => ['holding', 'unique_mapping', 'debit_fee', 'import_settings', 'allowed_payments_source']
    ];

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
        $query->add('where', $query->expr()->in($alias.'.type', array(GroupType::RENT, GroupType::GENERIC)));
        if (!empty($nHoldingId)) {
            $this->request->getSession()->set('holding_id', $nHoldingId);
            $query->andWhere($alias.'.holding_id = :holding_id');
            $query->setParameter('holding_id', $nHoldingId);
        }

        $id = $this->getRequest()->get('id', null);
        if (!empty($id)) {
            $query->andWhere($alias.'.id = :group_id');
            $query->setParameter('group_id', $id);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $object->setType(GroupType::RENT);
        $depositAccounts = $object->getDepositAccounts();
        /** @var DepositAccount $depositAccount */
        foreach ($depositAccounts as $depositAccount) {
            $depositAccount->setGroup($object);
            $depositAccount->setHolding($object->getHolding());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        $depositAccounts = $object->getDepositAccounts();
        /** @var DepositAccount $depositAccount */
        foreach ($depositAccounts as $depositAccount) {
            $depositAccount->setGroup($object);
            $depositAccount->setHolding($object->getHolding());
        }
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
                        ),
                        'import_properties' => [
                            'template' => 'AdminBundle:CRUD:list__action_import.html.twig'
                        ],
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $container = $this->getConfigurationPool()->getContainer();
        $formMapper
            ->with('Details')
                ->add(
                    'holding',
                    'sonata_type_model_reference', // Use a text field by cj_holding.id rather than a select drop-down
                    [
                        'label' => "Holding ID",
                        'model_manager' => $this->getModelManager(),
                        'class' => 'CreditJeeves\DataBundle\Entity\Holding'
                    ]
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
                ->add('statementDescriptor', null, ['label' => 'ID4', 'required' => true])
                ->add(
                    'orderAlgorithm',
                    'choice',
                    [
                        'choices' => OrderAlgorithmType::cachedTitles()
                    ]
                )
            ->end()
            ->with('Deposit Accounts')
                ->add(
                    'depositAccounts',
                    'sonata_type_collection',
                    [],
                    [
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable' => 'position',
                    ]
                )
            ->end()
            ->with('Group Phones')
                // override in /src/RentJeeves/AdminBundle/Resources/views/CRUD/group_edit.html.twig
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
                    $container->get('form.group_settings'),
                    [],
                    [
                        'edit'      => 'inline',
                        'inline'    => 'table',
                        'sortable'  => 'position',
                    ]
                )
            ->end()
            ->with('Import Defaults')
                ->add(
                    'importSettings',
                    'import_group_settings',
                    [],
                    [
                        'edit'      => 'inline',
                        'inline'    => 'table',
                        'sortable'  => 'position',
                    ]
                )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name');
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

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('cj_affiliate', null, array('route' => array('name' => 'show')))
            ->add('holding', null, array('route' => array('name' => 'show')))
            ->add('parent', null, array('route' => array('name' => 'show')))
            ->add('dealer', null, array('route' => array('name' => 'show')))
            ->add('name')
            ->add('code')
            ->add('description')
            ->add('website_url')
            ->add('logo_url')
            ->add('phone')
            ->add('fax')
//            ->add('fee_type')
            ->add('contract')
            ->add('contract_date')
            ->add('type')
            ->add('created_at')
            ->add('updated_at');
    }
}
