<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use RentJeeves\PublicBundle\Form\AddressType;

class AccountInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            null
        );
        $builder->add(
            'last_name',
            null
        );
        $builder->add(
            'email',
            null
        );
        $builder->add(
            'phone',
            null
        );

        $builder->add(
            'address',
            new AddressType()
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Landlord',
                'validation_groups' => array(
                    'invite',
                ),
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'account_info';
    }
}
