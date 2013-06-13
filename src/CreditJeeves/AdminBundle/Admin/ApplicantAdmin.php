<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
// use Sonata\AdminBundle\Datagrid\DatagridMapper;
// use Sonata\AdminBundle\Datagrid\ListMapper;
// use Sonata\AdminBundle\Show\ShowMapper;
// use Sonata\AdminBundle\Route\RouteCollection;

// use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

// use Knp\Menu\ItemInterface as MenuItemInterface;

class ApplicantAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'applicant';

    protected $formOptions = array(
            'validation_groups' => 'user_admin'
    );

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        
        $query->innerJoin($alias.'.scores', 's');
            
            //->addOrderBy('s.id',  'DESC');
            //
        
//         echo '<pre>';
//         print_r($query->execute(array(), Query::HYDRATE_ARRAY));
//         exit;
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_cj_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/cj/'.self::TYPE;
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
            ->add('current_score')
            ->add('user_leads')
            ->add('is_verified')
            ->add('is_active')
            ->add('has_report')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                        'report' => array(
                            'template' => 'AdminBundle:CRUD:list__action_report.html.twig'
                        ),
                        'observe' => array(
                            'template' => 'AdminBundle:CRUD:list__action_observe.html.twig'
                        ),
                    )
                )
            );
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email')
            ->add('first_name')
            ->add('middle_initial')
            ->add('last_name')
            ->add('is_verified');
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('first_name')
                ->add('middle_initial')
                ->add('last_name')
                ->add('email')
                ->add('is_verified')
                ->add('culture')
                ->add('user_leads', 'sonata_type_model', array('expanded' => true, 'multiple' => true))
            ->end();
    }
}
