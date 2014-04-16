<?php

namespace RentJeeves\PublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InviteLandlordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'landlord',
            new LandlordType()
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'invitelandlordtype';
    }
}
