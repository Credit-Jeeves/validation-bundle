<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class HoldingAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'holding';

    protected $formOptions = array(
            'validation_groups' => 'holding'
    );


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
            ->addIdentifier('name')
            ->add('groups')
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

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
           ->add('name');
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Holding')
                ->add('name')
            ->end()
            ->with('Groups')
                ->add('groups', 'sonata_type_model', array('expanded' => true, 'multiple' => true))
            ->end();
    }
}
