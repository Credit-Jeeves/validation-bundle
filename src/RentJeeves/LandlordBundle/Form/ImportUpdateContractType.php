<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Report\AccountingImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This form for exist user and exist contract, just update
 *
 * Class ImportUpdateUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportUpdateContractType extends AbstractType
{
    protected $accountingImport;

    public function __construct(AccountingImport $accountingImport)
    {
        $this->accountingImport = $accountingImport;
    }



    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            AccountingImport::KEY_RENT,
            'text',
            array(
                'attr'           => array(
                    'id'        => '',
                    'class'     => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_RENT,
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_RENT)
            )
        );

        $builder->add(
            AccountingImport::KEY_LEASE_END,
            'text',
            array(
                'attr'           => array(
                    'id'        => '',
                    'class'     => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_LEASE_END,
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_LEASE_END)
            )
        );

        $builder->add(
            AccountingImport::KEY_MOVE_OUT,
            'text',
            array(
                'attr'           => array(
                    'id'        => '',
                    'class'     => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_MOVE_OUT,
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_MOVE_OUT)
            )
        );

        $builder->add(
            AccountingImport::KEY_BALANCE,
            'text',
            array(
                'attr'           => array(
                    'id'        => '',
                    'class'     => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_BALANCE,
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_BALANCE)
            )
        );

        $builder->add(
            AccountingImport::KEY_RESIDENT_ID,
            'text',
            array(
                'attr'           => array(
                    'id'        => '',
                    'class'     => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_RESIDENT_ID,
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_RESIDENT_ID)
            )
        );

        $builder->add(
            '_token',
            'hidden',
            array(
                'attr'           => array(
                    'id'         => '',
                    'data-bind'  => 'value: _token'
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'cascade_validation' => true,
                'csrf_field_name' => '_token',
            )
        );
    }

    public function getName()
    {
        return 'import_update_contract';
    }
}

