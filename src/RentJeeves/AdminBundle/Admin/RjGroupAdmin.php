<?php
namespace RentJeeves\AdminBundle\Admin;

use RentJeeves\AdminBundle\Form\GroupSettings;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use CreditJeeves\DataBundle\Enum\GroupType;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use RentJeeves\CheckoutBundle\Controller\Traits\AccountAssociate;

class RjGroupAdmin extends Admin
{
    use AccountAssociate;

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
    public function preUpdate($object)
    {
        $em = $this->getModelManager()->getEntityManager($this->getClass());
        $oldDepositAccount = $object->getDepositAccount();
        $newMerchantName = $oldDepositAccount->getMerchantName();

        $oldMerchantName = $em->getUnitOfWork()
            ->getOriginalEntityData($oldDepositAccount)['merchantName'];

        /**
         * If we're changing the merchant name, we must unlink the old deposit
         * account from this group and create a new one.
         */
        if ($oldMerchantName != $newMerchantName) {

            // keep the old name, we'll make a new one next
            $oldDepositAccount->setMerchantName($oldMerchantName);

            // if we've already created the new one, then get it
            $newDepositAccount = $em->getRepository('RjDataBundle:DepositAccount')
                ->findOneByMerchantName($newMerchantName);

            // if not, create the new deposit account
            if (!$newDepositAccount) {
                $newDepositAccount = new DepositAccount();
                $newDepositAccount->setMerchantName($newMerchantName);
                $newDepositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
                $em->persist($newDepositAccount);

                // save it now because we need it to have a database id
                $em->flush();
            }

            /**
             * find all the payment accounts registered to the old deposit
             * account *but* not the new one
             */
            $paymentAccountWorkingSet = $em
                ->getRepository('RjDataBundle:PaymentAccount')
                ->findByDepositAccountId($oldDepositAccount->getId());

            /**
             * Register each payment account with the new deposit account and
             * merchant
             */
            foreach ($paymentAccountWorkingSet as $paymentAccount) {
                if ($paymentAccount->getDepositAccounts()->contains($newDepositAccount)) {
                    continue;
                }

                $this->registerTokenToAdditionalMerchant(
                    $oldMerchantName,
                    $newMerchantName,
                    $paymentAccount->getToken()
                );

                $paymentAccount->addDepositAccount($newDepositAccount);
                $em->persist($paymentAccount);

                // flush each one because this could fail and we want to
                $em->flush();
            }

            /**
             * Remove the group from the old deposit account and add it to the
             * new one
             */
            // finally associate the group to the new deposit account
            $oldDepositAccount->setGroup(null);
            $newDepositAccount->setGroup($object);
            $em->persist($oldDepositAccount);
            $em->persist($newDepositAccount);
            $em->flush();
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
                'depositAccount',
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
                ->add('depositAccount.merchantName', null, array('label' => 'Merchant Name'))
                ->add(
                    'depositAccount.feeCC',
                    'number',
                    array('label' => 'CC Fee (%)', 'required' => false)
                )
                ->add(
                    'depositAccount.feeACH',
                    'number',
                    array('label' => 'ACH Fee ($)', 'required' => false)
                )
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
