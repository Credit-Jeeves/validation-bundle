<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

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

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
        $collection->remove('export');
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('first_name')
            ->add('middle_initial')
            ->add('last_name')
            ->add('email')
            ->add('phone')
            ->add('current_score')
            ->add(
                'user_leads',
                null,
                array(
                    'route' => array(
                        'name' => 'show'
                    )
                )
            )
            ->add('is_verified')
            ->add('is_active')
            ->add('has_report')
            ->add('last_login', 'date')
            ->add('created_at', 'date')
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
                            'template' => 'AdminBundle:CRUD:list__applicant_observe.html.twig'
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
            ->end();
    }
}
