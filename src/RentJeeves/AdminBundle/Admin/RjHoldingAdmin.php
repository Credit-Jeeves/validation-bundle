<?php
namespace RentJeeves\AdminBundle\Admin;

use CreditJeeves\AdminBundle\Admin\CjHoldingAdmin as Admin;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;

class RjHoldingAdmin extends Admin
{
    /**
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);
        $container = $this->getConfigurationPool()->getContainer();
        $formMapper
            ->add(
                'isPaymentProcessorLocked',
                null,
                [
                    'required' => false,
                    'label' => 'admin.holding.payment_processor_locked',
                ]
            )
            ->add(
                'paymentsEnabled',
                null,
                [
                    'required' => false,
                    'label' => 'admin.holding.payments_enabled',
                ]
            )
            ->add(
                'postAppFeeAndSecurityDeposit',
                null,
                [
                    'required' => false,
                    'label' => 'admin.holding.post_app_fee_security_deposit',
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
                ->add(
                    'useRecurringCharges',
                    null,
                    [
                        'required' => false,
                        'label' => 'admin.holding.use_recurring_charges',
                    ]
                )
                ->add(
                    'recurringCodes',
                    null,
                    [
                        'required' => false,
                        'label' => 'admin.holding.recurring_codes',
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
            ->with('Promas Settings')
                ->add(
                    'exportTenantId',
                    null,
                    [
                        'required' => false,
                        'label' => 'admin.holding.export_tenant_id',
                    ]
                )
            ->with('Profit Stars')
                ->add(
                    'profitStarsSettings',
                    'profit_stars_settings_type',
                    [
                        'required' => false,
                    ]
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

        $profitStarsSettings = $holding->getProfitStarsSettings();
        if ($profitStarsSettings) {
            $profitStarsSettings->setHolding($holding);
        } else {
            $holding->setProfitStarsSettings(null);
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
