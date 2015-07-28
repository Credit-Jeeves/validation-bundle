<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\AdminBundle\Admin\CjHoldingAdmin as Admin;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\DataBundle\Entity\YardiSettings;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
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
                $alias . '_g.type',
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
        $container = $this->getConfigurationPool()->getContainer();
        $formMapper
            ->add(
                'isPaymentProcessorLocked',
                'choice',
                [
                    'label' => 'admin.label.payment_processor_locked',
                    'choices'           => [
                        0 => 'No',
                        1 => 'Yes'
                    ],
                ]
            )
            ->with('Accounting Settings')
            ->add(
                'apiIntegrationType',
                'choice',
                [
                    'choices'           => array_map(
                        'ucwords',
                        ApiIntegrationType::cachedTitles()
                    )
                ]
            )
            ->with('Yardi Settings')
            ->add(
                'yardiSettings',
                $container->get('form.yardi_settings'),
                array(
                    'required' => false,
                ),
                array(
                    'edit'      => 'inline',
                    'inline'    => 'table',
                    'sortable'  => 'position',
                )
            )
            ->with('ResMan Settings')
            ->add(
                'resManSettings',
                $container->get('form.resman_settings'),
                array(
                    'required' => false,
                ),
                array(
                    'edit'      => 'inline',
                    'inline'    => 'table',
                    'sortable'  => 'position',
                )
            )
            ->with('MRI Settings')
            ->add(
                'mriSettings',
                $container->get('form.mri_settings'),
                array(
                    'required' => false,
                ),
                array(
                    'edit'      => 'inline',
                    'inline'    => 'table',
                    'sortable'  => 'position',
                )
            )
            ->with('AMSI Settings')
            ->add(
                'amsiSettings',
                'amsiSettings',
                array(
                    'required' => false,
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
        $this->setHolding($holding);
    }

    public function preUpdate($holding)
    {
        $this->setHolding($holding);
    }

    /**
     * @param Holding $holding
     */
    protected function setHolding(Holding $holding)
    {
        $yardi = $holding->getYardiSettings();
        if ($yardi && $yardi->getUrl()) {
            $yardi->setHolding($holding);
        } else {
            $holding->setYardiSettings(null);
        }

        $resMan = $holding->getResManSettings();
        if ($resMan && $resMan->getAccountId()) {
            $resMan->setHolding($holding);
        } else {
            $holding->setResManSettings(null);
        }

        $mriSettings = $holding->getMriSettings();
        if ($mriSettings && $mriSettings->getUser()) {
            $mriSettings->setHolding($holding);
        } else {
            $holding->setMriSettings(null);
        }

        $amsiSettings = $holding->getAmsiSettings();
        if ($amsiSettings && $amsiSettings->getUser()) {
            $amsiSettings->setHolding($holding);
        } else {
            $holding->setAmsiSettings(null);
        }
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
