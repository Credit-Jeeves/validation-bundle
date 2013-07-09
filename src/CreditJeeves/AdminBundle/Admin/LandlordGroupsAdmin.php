<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class LandlordGroupsAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'group';

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
            $query->andWhere($alias.'.type = :type');
            $query->setParameter('type', 'rent');
        return $query;
    }


//     protected $formOptions = array(
//             'validation_groups' => 'holding'
//     );

    /**
     * {@inheritdoc}
     */
    public function getBaseRouteName()
    {
        return 'admin_rj_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRoutePattern()
    {
        return '/rj/'.self::TYPE;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
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
