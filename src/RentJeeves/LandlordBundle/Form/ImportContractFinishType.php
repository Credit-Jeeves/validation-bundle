<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Accounting\AccountingImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This form for exit Tenant and new Contract
 *
 * Class ImportNewUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportContractFinishType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'finishAt',
            'date',
            array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
            )
        );

        $builder->add(
            '_token',
            'hidden',
            array(
                'mapped' => false,
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => array(
                    'import',
                ),
                'csrf_protection'    => false,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_contract_finish';
    }
}
