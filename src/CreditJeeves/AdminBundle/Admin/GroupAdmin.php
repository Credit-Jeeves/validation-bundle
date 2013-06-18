<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class GroupAdmin extends Admin
{
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
        return 'admin_cj_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/cj/'.self::TYPE;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('holding')
            ->add('type')
            ->add('fee_type')
            ->add('count_leads')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                        'leads' => array(
                            'template' => 'AdminBundle:CRUD:list__action_leads.html.twig'
                        ),
                        'dealers' => array(
                            'template' => 'AdminBundle:CRUD:list__action_dealers.html.twig'
                        ),
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name');
    }
}
