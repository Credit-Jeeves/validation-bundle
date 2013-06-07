<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\QueryBuilder;


use Knp\Menu\ItemInterface as MenuItemInterface;

class LandlordAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'landlord';

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->getQueryBuilder()->andWhere('o.type = :type')->setParameter('type', self::TYPE);
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_rj_user_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/rj/user/'.self::TYPE;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('full_name')
            ->add('email')
            ->add('is_active')
            ->add('last_login', 'date')
            ->add(
                    '_action',
                    'actions',
                    array(
                            'actions' => array(
                                    'edit' => array(),
                                    'delete' => array(),
                            )
                    )
            );
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('first_name')
                ->add('middle_initial', null, array('required' => false))
                ->add('last_name')
                ->add('email')
                ->add('password', 'hidden',array('required' => false))
                ->add('password_new', 'password', array('required' => false, 'mapped' => false))
                ->add('password_retype', 'password', array('required' => false, 'mapped' => false))
                ->add('is_active', null, array('required' => false))
//                ->add('is_super_admin', null, array('required' => false))
            ->end();
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('is_active');
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
            $request->getSession()->getFlashBag()->add('sonata_flash_error', 'Please, enter password for '.$user->getFullName() );
        }
        return $user;
    }
}
