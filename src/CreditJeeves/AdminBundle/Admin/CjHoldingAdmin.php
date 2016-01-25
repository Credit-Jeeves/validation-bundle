<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CjHoldingAdmin extends Admin
{
    protected $formOptions = array(
            'validation_groups' => 'holding'
    );

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, array('route' => array('name' => 'show')))
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
            ->add('name');
    }

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
