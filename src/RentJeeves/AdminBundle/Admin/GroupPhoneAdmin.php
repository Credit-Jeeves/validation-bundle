<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class GroupPhoneAdmin extends Admin
{

    /**
     * @var array
     */
    protected $formOptions = [
        'validation_groups' => ['sonata']
    ];

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('phone')
            ->add('isActive')
            ->add('group.name')
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
            ->add('phone')
            ->add(
                'group',
                'sonata_type_model_reference', // Use a text field by rj_group.id rather than a select drop-down
                [
                    'label' => "Group ID",
                    'model_manager' => $this->getModelManager(),
                    'class' => 'CreditJeeves\DataBundle\Entity\Group'
                ]
            )
            ->add(
                'isActive',
                null,
                array(
                    'required' => false
                )
            );
    }
}
