<?php 
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class UserAdmin extends Admin
{
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('email')
             ->add('first_name')
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
         $listMapper
//             ->addIdentifier('title')
             ->add('first_name')
             ->add('middle_initial')
             ->add('last_name')
//             ->add('commentsEnabled')
         ;
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
