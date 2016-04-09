<?php
namespace RentJeeves\AdminBundle\Admin;

use Doctrine\ORM\EntityManager;
use RentJeeves\CoreBundle\ContractManagement\ContractManager;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\ContractStatus;
use RentJeeves\TenantBundle\Form\DataTransformer\PhoneNumberTransformer;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class TenantAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'tenant';

    protected $formOptions = array(
        'validation_groups' => 'user_admin'
    );

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/'.self::TYPE;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add(
            'unlock',
            $this->getRouterIdParameter().'/unlock'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $id = $this->getRequest()->get('id', null);

        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();

        if (!empty($id)) {
            $query->andWhere($alias.'.id = :user_id');
            $query->setParameter('user_id', $id);
        }

        return $query;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $this->request->getSession()->set('contract_id', null);
        $this->request->getSession()->set('user_id', null);
        $listMapper
            ->addIdentifier('id', null, array('route' => array('name' => 'show')))
            ->add('first_name')
            ->add('middle_initial')
            ->add('last_name')
            ->add('email', 'email')
            ->add(
                'phone',
                'phone',
                [
                    'template' => 'AdminBundle:CRUD:list__phone_number.html.twig'
                ]
            )
            ->add(
                'is_verified',
                'string',
                array(
                    'template' => 'AdminBundle:CRUD:tenant_verification_status_choice.html.twig'
                )
            )
            ->add('is_active')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                        'observe' => array(
                            'template' => 'AdminBundle:CRUD:list__tenant_observe.html.twig'
                        ),
                        'contracts' => array(
                            'template' => 'AdminBundle:CRUD:list__tenant_contracts.html.twig'
                        ),
                        'payments' => array(
                            'template' => 'AdminBundle:CRUD:list__tenant_orders.html.twig'
                        ),
                        'report' => array(
                            'template' => 'AdminBundle:CRUD:list__action_report.html.twig'
                        ),
                        'newReport' => array(
                            'template' => 'AdminBundle:CRUD:list__action_new_report.html.twig'
                        ),
                    )
                )
            );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('is_active')
            ->add('first_name')
            ->add('last_name')
            ->add('email');
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Profile')
                ->add(
                    'first_name'
                )
                ->add(
                    'middle_initial'
                )
                ->add(
                    'last_name'
                )
                ->add(
                    'email'
                )
                ->add(
                    $formMapper->create(
                        'phone',
                        'text',
                        [
                            'required' => false
                        ]
                    )->addViewTransformer(new PhoneNumberTransformer())
                )
                ->add(
                    'password',
                    'hidden',
                    array(
                        'required' => false
                    )
                )
                ->add(
                    'password_new',
                    'password',
                    array(
                        'required' => false,
                        'mapped' => false
                    )
                )
                ->add(
                    'password_retype',
                    'password',
                    array(
                        'required' => false,
                        'mapped' => false
                    )
                )
                ->add(
                    'culture'
                )
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($user)
    {
        $user = $this->checkPassword($user);
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($user)
    {
        /** @var Tenant $user */
        if (false === empty($user->getEmail())) {
            $container = $this->getConfigurationPool()->getContainer();
            /** @var EntityManager $em */
            $em = $container->get('doctrine.orm.default_entity_manager');
            /** @var ContractManager $contractManager */
            $contractManager = $container->get('renttrack.contract_manager');
            $contracts = $em->getRepository('RjDataBundle:Contract')->getAllWaitingForTenant($user);
            foreach ($contracts as $contract) {
                $contractManager->moveContractOutOfWaiting($contract, ContractStatus::APPROVED, $user->getEmail());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($user)
    {
        $user->setType(self::TYPE);
        $user = $this->checkPassword($user);
    }

    private function checkPassword($user)
    {
        $isValid = false;
        $password = $user->getPassword();
        $request = $this->getRequest();
        $formData = $request->request->get($this->getUniqid());
        $password_new = $formData['password_new'];
        $password_retype = $formData['password_retype'];
        if (!empty($password)) {
            $isValid = true;
        }
        if (!empty($password_new) && $password_new === $password_retype) {
            $isValid = true;
            $user->setPassword(md5($password_new));
        }
        if (!$isValid) {
            $request->getSession()->getFlashBag()->add('sonata_flash_error', 'Please, enter password for this admin');
        }

        return $user;
    }

    protected function configureShowFields(ShowMapper $formMapper)
    {
        $formMapper
            ->add('holding', null, array('route' => array('name' => 'show')))
            ->add('type')
            ->add('username')
            ->add('usernameCanonical')
            ->add('email')
            ->add('emailCanonical')
            ->add('enabled')
            ->add('lastLogin')
            ->add('locked')
            ->add('expired')
            ->add('expiresAt')
            ->add('confirmationToken')
            ->add('passwordRequestedAt')
            ->add('roles')
            ->add('credentialsExpired')
            ->add('credentialsExpireAt')
            ->add('firstName')
            ->add('middleInitial')
            ->add('lastName')
            ->add('date_of_birth')
            ->add('ssh')
            ->add('isActive')
            ->add('inviteCode')
            ->add('scoreChangedNotification')
            ->add('offerNotification')
            ->add('culture')
            ->add('hasData') // Sets to 0 when user left
            ->add('isVerified')
            ->add('hasReport')
            ->add('isHoldingAdmin')
            ->add('isSuperAdmin')
            ->add('created_at')
            ->add('updated_at');
    }
}
