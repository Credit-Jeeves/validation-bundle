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
class ImportTenantType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            'text',
            array(
                'attr'           => array(
                    'class'     => 'half-width',
                    'data-bind' => 'value: first_name',
                ),
            )
        );


        $builder->add(
            'last_name',
            'text',
            array(
                'attr'           => array(
                    'class'     => 'half-width',
                    'data-bind' => 'value: last_name',
                ),
            )
        );

        $builder->add(
            'residentId',
            'text',
            array(
                'attr'           => array(
                    'class'     => 'half-width',
                    'data-bind' => 'value: residentId',
                ),
            )
        );

        $builder->add(
            'email',
            'text',
            array(
                'attr'           => array(
                    'class'     => 'half-width',
                    'data-bind' => 'value: email',
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
                'validation_groups' => array(
                    'import_tenant',
                ),
                'csrf_protection'    => true,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_new_tenant';
    }
}

