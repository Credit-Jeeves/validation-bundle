<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\DataBundle\Validators\TenantEmail;
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
            array()
        );


        $builder->add(
            'last_name',
            'text',
            array()
        );

        $builder->add(
            'email',
            'text',
            array(
                'constraints'    => array(
                    new TenantEmail(
                        array(
                            'groups' => 'import',
                        )
                    )
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
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
        return 'import_new_tenant';
    }
}

