<?php

namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\DataBundle\Form\ChoiceList\StateChoiceList;
use CreditJeeves\ApplicantBundle\Form\Type\SsnType;

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
//         $builder->add(
//                 'ssn',
//                 'text', array('property_path' => new SsnType())
//         );
//         $builder->add(
//             'ssn1',
//             'text',
//             array(
//                 'label' => 'SSN'
//                 )
//         );
//         $builder->add(
//             'ssn2',
//             'text',
//             array(
//                 'label' => ''
//                 )
//         );
//         $builder->add(
//             'ssn3',
//             'text',
//             array(
//                 'label' => '0'
//                 )
//         );
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
        $builder->add(
            'state',
            'choice',
            array(
                'label' => '',
                'choice_list' =>  new StateChoiceList(),
                )
        );
        $builder->add(
            'zip',
            'text',
            array(
                    'label' => ''
                )
        );
        $builder->add(
            'phone_type',
            'choice',
            array(
                'label' => '',
                'choices' => array(
                    '1' => 'Mobile',
                    '2' => 'Home',
                    '3' => 'Work',
                    ),
                )
        );
        $builder->add(
            'phone',
            'text',
            array(
                    'label' => 'Phone'
                )
        );
        $builder->add(
            'tos',
            'hidden',
            array(
                'label' => '',
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'CreditJeeves\DataBundle\Entity\User'));
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_usertype';
    }
}
