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
    
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nGroupId = $this->getRequest()->get('group_id', $this->request->getSession()->get('group_id', null));
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        if (!empty($nGroupId)) {
            $this->request->getSession()->set('group_id', $nGroupId);
            $query->innerJoin($alias.'.dealer_groups', 'g');
            $query->andWhere('g.id = :group_id');
            $query->setParameter('group_id', $nGroupId);
        }
        return $query;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('full_name')
            ->add('holding')
            ->add('email')
            ->add('is_active')
//            ->add('last_login')
            ->add('is_super_admin')
            ->add('dealer_groups')
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
                        'observe' => array(
                            'template' => 'AdminBundle:CRUD:list__dealer_observe.html.twig'
                        ),
                    )
                )
            );
    }
    
    
    /**
     *
     * @var string
     */
    
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('email')
             ->add('full_name');
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


    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
//        $datagridMapper
//            ->add('type')//             ->add('enabled')
////             ->add('tags', null, array('filter_field_options' => array('expanded' => true, 'multiple' => true)))
//        ;
    }
}
