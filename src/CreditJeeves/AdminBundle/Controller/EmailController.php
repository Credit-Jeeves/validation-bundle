<?php 
namespace CreditJeeves\AdminBundle\Controller;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EmailAdmin extends Admin
{
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
//             ->add('enabled')
//             ->add('title')
//             ->add('abstract')
//             ->add('content')
//             ->add('tags')
        ;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
//         $formMapper
//             ->with('General')
//                 ->add('enabled', null, array('required' => false))
//                 ->add('author', 'sonata_type_model', array(), array('edit' => 'list'))
//                 ->add('title')
//                 ->add('abstract')
//                 ->add('content')
//             ->end()
//             ->with('Tags')
//                 ->add('tags', 'sonata_type_model', array('expanded' => true))
//             ->end()
//             ->with('Options', array('collapsed' => true))
//                 ->add('commentsCloseAt')
//                 ->add('commentsEnabled', null, array('required' => false))
//             ->end()
//         ;
    }

    public function configureListFields(ListMapper $listMapper)
    {
//         $listMapper
//             ->addIdentifier('title')
//             ->add('author')
//             ->add('enabled')
//             ->add('tags')
//             ->add('commentsEnabled')
//         ;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
//         $datagridMapper
//             ->add('title')
//             ->add('enabled')
//             ->add('tags', null, array('filter_field_options' => array('expanded' => true, 'multiple' => true)))
//         ;
    }
}