<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Report\AccountingImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This form for new Tenant, new Contract
 *
 * Class ImportNewUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportNewUserWithContractType extends AbstractType
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
                    'class' => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_RENT
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_RENT)
            )
        );

        $builder->add(
            AccountingImport::KEY_LEASE_END,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_LEASE_END
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_LEASE_END)
            )
        );

        $builder->add(
            AccountingImport::KEY_BALANCE,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_BALANCE
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_BALANCE)
            )
        );

        $builder->add(
            AccountingImport::KEY_EMAIL,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_EMAIL
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_EMAIL)
            )
        );

        $builder->add(
            AccountingImport::FIRST_NAME_TENANT,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::FIRST_NAME_TENANT
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_TENANT_NAME)
            )
        );

        $builder->add(
            AccountingImport::LAST_NAME_TENANT,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::LAST_NAME_TENANT
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_TENANT_NAME)
            )
        );

        $builder->add(
            AccountingImport::KEY_MOVE_IN,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_MOVE_IN
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_MOVE_IN)
            )
        );

        $builder->add(
            AccountingImport::KEY_RESIDENT_ID,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_RESIDENT_ID
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_RESIDENT_ID)
            )
        );

        $builder->add(
            AccountingImport::KEY_UNIT,
            'text',
            array(
                'attr'           => array(
                    'class'     => 'half-width',
                    'data-bind' => 'value: '.AccountingImport::KEY_UNIT
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_UNIT)
            )
        );

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_new_user_with_contract';
    }
}

