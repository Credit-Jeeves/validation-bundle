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
class ImportContractType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'startAt',
            'date',
            array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'attr'           => array(
                    'class'     => 'half-width',
                ),
            )
        );


        $builder->add(
            'finishAt',
            'date',
            array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'attr'           => array(
                    'class'     => 'half-width',
                ),
            )
        );

        $builder->add(
            'imported_balance',
            'text',
            array(
                'attr'           => array(
                    'class'     => 'half-width',
                ),
            )
        );

        $builder->add(
            'rent',
            'text',
            array(
                'attr'           => array(
                    'class'     => 'half-width',
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => array(
                    'import_contract',
                ),
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_tenant';
    }
}

