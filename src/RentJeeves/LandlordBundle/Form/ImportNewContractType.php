<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Report\AccountingImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This form for exit Tenant and new Contract
 *
 * Class ImportNewUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportNewContractType extends AbstractType
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
                    'class' => 'half-width'
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_RENT)
            )
        );

        $builder->add(
            AccountingImport::KEY_LEASE_END,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width'
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_LEASE_END)
            )
        );

        $builder->add(
            AccountingImport::KEY_BALANCE,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width'
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_BALANCE)
            )
        );

        $builder->add(
            AccountingImport::KEY_MOVE_IN,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width'
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_MOVE_IN)
            )
        );

        $builder->add(
            AccountingImport::KEY_RESIDENT_ID,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width'
                ),
                'constraints'    => $this->accountingImport->getValidatorsByKey(AccountingImport::KEY_RESIDENT_ID)
            )
        );

        $builder->add(
            AccountingImport::KEY_UNIT,
            'text',
            array(
                'attr'           => array(
                    'class' => 'half-width'
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
        return 'import_new_contract';
    }
}

