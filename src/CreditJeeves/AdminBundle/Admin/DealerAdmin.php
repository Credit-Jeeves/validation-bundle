<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\QueryBuilder;

use Knp\Menu\ItemInterface as MenuItemInterface;

class DealerAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'dealer';
    
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
        return 'admin_cj_'.self::TYPE;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/cj/'.self::TYPE;
    }
    
    /**
     *
     * @var string
     */
    
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('email')
             ->add('full_name')
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
            ->add('last_name')//             ->add('type')
//             ->add('commentsEnabled')
        ;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('type')//             ->add('enabled')
//             ->add('tags', null, array('filter_field_options' => array('expanded' => true, 'multiple' => true)))
        ;
    }
}
