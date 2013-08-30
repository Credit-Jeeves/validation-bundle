<?php
namespace RentJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use CreditJeeves\DataBundle\Enum\GroupType;

class RjGroupAdmin extends Admin
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
        return 'admin_rj_'.self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nHoldingId = $this->getRequest()->get('holding_id', $this->request->getSession()->get('holding_id', null));
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        $query->add('where', $query->expr()->in($alias.'.type', array(GroupType::RENT)));
        if (!empty($nHoldingId)) {
            $this->request->getSession()->set('holding_id', $nHoldingId);
            $query->andWhere($alias.'.holding_id = :holding_id');
            $query->setParameter('holding_id', $nHoldingId);
        }
        
        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $object->setType(GroupType::RENT);
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
            ->add('holding')
            ->add('affiliate')
            ->add('count_properties')
            ->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'edit' => array(),
                        'delete' => array(),
                        'tenants' => array(
                            'template' => 'AdminBundle:CRUD:list__action_tenants.html.twig'
                        ),
                        'landlords' => array(
                            'template' => 'AdminBundle:CRUD:list__action_landlords.html.twig'
                        ),
                    )
                )
            );
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add(
                'holding',
                'sonata_type_model'
            )
            ->add(
                'affiliate',
                'sonata_type_model',
                array(
                    'empty_value' => 'None'
                )
            )
            ->add('name');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name');
    }
}
