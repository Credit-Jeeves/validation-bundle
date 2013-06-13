<?php
namespace CreditJeeves\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class LeadAdmin extends Admin
{
    /**
     *
     * @var string
     */
    const TYPE = 'lead';

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
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $nUserId = $this->getRequest()->get('user_id', null);
        $nGroupId = $this->getRequest()->get('group_id', null);
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        if (!empty($nUserId)) {
            $query->andWhere($alias.'.cj_account_id = :user_id');
            $query->setParameter('user_id', $nUserId);
        }
        if (!empty($nGroupId)) {
            $query->andWhere($alias.'.cj_group_id = :group_id');
            $query->setParameter('group_id', $nGroupId);
        }
        return $query;
    }
    
    
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('target_score');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('target_score');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('target_score');
    }
}
