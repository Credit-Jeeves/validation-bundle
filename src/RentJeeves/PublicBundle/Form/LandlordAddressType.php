<?php

namespace RentJeeves\PublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use RentJeeves\PublicBundle\Form\AddressType;
use RentJeeves\PublicBundle\Form\LandlordType;
use RentJeeves\PublicBundle\Form\BankAccountType;

class LandlordAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'address',
            new AddressType()
        );
        $builder->add(
            'landlord',
            new LandlordType()
        );

        $builder->add(
            'property',
            'hidden'
        );

        $builder->add(
            'units',
            'collection',
            array(
                'type'          => 'text',
                'required'      => false,
                'allow_add'     => true,
                'options'       => array(
                    'required'  => false,
                    'attr'      => array('class' => 'unit-box')
                ),
            )
        );

        $builder->add(
            'deposit',
            new BankAccountType()
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'LandlordAddressType';
    }
}
