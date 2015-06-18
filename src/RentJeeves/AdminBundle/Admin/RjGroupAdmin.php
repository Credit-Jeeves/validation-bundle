<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use CreditJeeves\DataBundle\Enum\GroupType;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RjGroupAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'group';

    protected $formOptions = array(
            'validation_groups' => ['holding', 'unique_mapping']
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
                'depositAccount',
                'sonata_type_model',
                array(
                    'empty_value' => 'None',
                    'required' => false,
                    'label' => 'Merchant status'
                )
            )
            ->add('disableCreditCard')
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
        $container = $this->getConfigurationPool()->getContainer();
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
                ->add('statementDescriptor', null, ['label' => 'ID4', 'required' => true])
                ->add('mailingAddressName')
            ->end()
            ->with('Deposit Account')
                // admin.deposit_account.merchant_name
                ->add('depositAccount.merchantName', null, array('label' => 'Merchant Name', 'required' => false))
                ->add(
                    'accountNumberMapping.accountNumber',
                    null,
                    array(
                        'label' => 'Account Number',
                        'required' => false,
                    )
                )
                ->add(
                    'depositAccount.status',
                    'choice',
                    array(
                        'label' => 'Status',
                        'required' => false,
                        'choices' => DepositAccountStatus::cachedTitles()
                    )
                )
                ->add(
                    'depositAccount.feeCC',
                    'number',
                    array('label' => 'CC Fee (%)', 'required' => false) //admin.deposit_account.fee_cc
                )
                ->add(
                    'depositAccount.feeACH',
                    'number',
                    array('label' => 'ACH Fee ($)', 'required' => false) //admin.deposit_account.fee_ach
                )
                ->add(
                    'depositAccount.mid',
                    'number',
                    ['label' => 'Mid', 'required' => false]
                )
                ->add(
                    'depositAccount.passedAch',
                    'checkbox',
                    ['label' => 'Is passed ach', 'required' => false]
                )
                ->add('disableCreditCard', 'checkbox', ['label' => 'Disable Credit Card?', 'required' => false])
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
                    $container->get('form.group_settings'),
                    array(
                    ),
                    array(
                        'edit'      => 'inline',
                        'inline'    => 'table',
                        'sortable'  => 'position',
                    )
                )
            ->end();

        $self = $this;
        $formMapper->getFormBuilder()->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) use ($self) {
                $form = $event->getForm();
                /** @var Group $group */
                $group = $form->getData();
                $accountMapping = $group->getAccountNumberMapping();
                if (!$accountMapping->getHolding()) {
                    $holding = $group->getHolding();
                    $accountMapping->setHolding($holding);
                }
            }
        );
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('depositAccount.status', null, array('label' => 'Merchant status'));
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
            ->add('street_address_1')
            ->add('street_address_2')
            ->add('city')
            ->add('state')
            ->add('zip')
//            ->add('fee_type')
            ->add('contract')
            ->add('contract_date')
            ->add('type')
            ->add('created_at')
            ->add('updated_at');
    }
}
