<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\True;

class UserInvitePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'date_of_birth',
            'birthday'
        );
        $builder->add(
            'password',
            'repeated',
            array(
                'first_name' => 'Password',
                'second_name' => 'Retype',
                'type' => 'password',
                )
        );
        $builder->add(
            'tos',
            'hidden',
            array(
                'label' => '',
                'data' => 0,
                'mapped' => false,
                'constraints' => new True(
                    array(
                        'message' => 'error.user.tos',
                        'groups' => 'registration_tos'
                    )
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\User',
                'validation_groups' => array(
                    'registration_tos'
                ),
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'intention'       => 'username',
            )
        );
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_newpasswordtype';
    }
}
