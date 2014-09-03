<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\AdminBundle\Admin\CjHoldingAdmin as Admin;
use RentJeeves\DataBundle\Entity\YardiSettings;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use CreditJeeves\DataBundle\Enum\GroupType;

class RjHoldingAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $alias = $query->getRootAlias();
        $query->add(
            'where',
            $query->expr()->in(
                $alias.'_g.type',
                array(
                    GroupType::RENT
                )
            )
        );
        return $query;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);
        $contrainer = $this->getConfigurationPool()->getContainer();
        $formMapper
            ->with('Yardi Settings')
                ->add(
                    'yardiSettings',
                    $contrainer->get('form.yardi_settings'),
                    array(
                    ),
                    array(
                        'edit'      => 'inline',
                        'inline'    => 'table',
                        'sortable'  => 'position',
                    )
                )
            ->end();
    }

    public function prePersist($holding)
    {
        /**
         * @var $yardi YardiSettings
         */
        $yardi = $holding->getYardiSettings();
        $yardi->setHolding($holding);
    }

    protected function configureShowField(ShowMapper $showMapper)
    {
        parent::configureShowField($showMapper);
        $showMapper
            ->add('users', null, array('route' => array('name' => 'show')))
            ->add('groups', null, array('route' => array('name' => 'show')))
            ->add('units', null, array('route' => array('name' => 'show')))
            ->add('contracts', null, array('route' => array('name' => 'show')));
    }
}
