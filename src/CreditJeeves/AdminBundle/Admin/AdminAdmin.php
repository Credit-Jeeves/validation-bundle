<?php 
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\QueryBuilder;
use FOS\UserBundle\Model\UserManagerInterface;

class AdminAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'admin';

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
            ->add('is_super_admin')
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
                ->add('password', 'text', array('required' => false))
                ->add('is_active', null, array('required' => false))
                ->add('is_super_admin', null, array('required' => false))
            ->end();
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('is_active')
            ->add('is_super_admin');
    }

    public function preUpdate($user)
    {
        $this->getUserManager()->updateCanonicalFields($user);
        $this->getUserManager()->updatePassword($user);
    }
    
    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }
    
    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }
}
