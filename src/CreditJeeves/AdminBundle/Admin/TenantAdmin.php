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

    /**
     * {@inheritdoc}
     */
    public function prePersist($user)
    {
        $user->setType(self::TYPE);
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('first_name')
            ->add('middle_initial')
            ->add('last_name')
            ->add('email')
            ->add('state')
            ->add('zip')
            ->add('city')
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
                    )
                )
            );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('is_active')
            ->add('state')
            ->add('city');
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Profile')
            ->add('first_name')
            ->add('middle_initial')
            ->add('last_name')
            ->add('email')
            ->add('culture')
            ->end();
    }
}
