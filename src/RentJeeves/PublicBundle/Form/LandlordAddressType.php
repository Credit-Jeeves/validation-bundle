<?php

namespace RentJeeves\PublicBundle\Form;

use RentJeeves\DataBundle\Validators\SinglePropertyConstraint as SingleProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use RentJeeves\PublicBundle\Form\AddressType;
use RentJeeves\PublicBundle\Form\LandlordType;
use Symfony\Component\Validator\Constraints\Count;

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
            new LandlordType(),
            array(
                'inviteEmail' => $options['inviteEmail']
            )
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
                'error_bubbling' => false,
                'options'       => array(
                    'required'  => false,
                    'attr'      => array('class' => 'unit-name'),
                )
            )
        );

        $builder->add(
            'isSingleProperty',
            'checkbox',
            array(
                'label'         => 'landlord.register.single_property',
                'required'      => false,
                'constraints'   => new SingleProperty(),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
                'inviteEmail'        => false,
            )
        );
    }

    public function getName()
    {
        return 'LandlordAddressType';
    }
}
