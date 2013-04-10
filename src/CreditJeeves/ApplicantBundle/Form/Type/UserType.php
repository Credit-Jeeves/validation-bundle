<?php

namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            'text',
            array(
                'label' => 'Name'
                )
            );
        $builder->add(
            'middle_initial',
            'text',
            array(
                'label' => '',
                'required' => false,
                )
            );
        $builder->add(
            'last_name',
            'text',
            array(
                'label' => ''
                )
            );
        $builder->add(
            'ssn1',
            'text',
            array(
                'label' => 'SSN'
                )
            );
        $builder->add(
            'ssn2',
            'text',
            array(
                'label' => ''
                )
            );
        $builder->add(
            'ssn3',
            'text',
            array(
                'label' => '0'
                )
            );
        $builder->add(
            'street_address1',
            'text',
            array(
                'label' => 'Address',
                )
            );
        $builder->add(
            'unit_no',
            'text',
            array(
                'label' => '',
                )
            );
        $builder->add(
            'city',
            'text',
            array(
                'label' => ''
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CreditJeeves\DataBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_usertype';
    }
}
