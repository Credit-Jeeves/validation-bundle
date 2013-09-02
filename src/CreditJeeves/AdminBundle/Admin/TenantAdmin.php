<?php 
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\QueryBuilder;

use Knp\Menu\ItemInterface as MenuItemInterface;

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
        return 'admin_rj_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/rj/'.self::TYPE;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $this->request->getSession()->set('contract_id', null);
        $this->request->getSession()->set('user_id', null);
        $listMapper
            ->add('first_name')
            ->add('middle_initial')
            ->add('last_name')
            ->add('email')
            ->add('phone')
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
                        )
                    )
                )
            );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('is_active');
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
}
