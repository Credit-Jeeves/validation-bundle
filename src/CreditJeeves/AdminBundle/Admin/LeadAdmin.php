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
