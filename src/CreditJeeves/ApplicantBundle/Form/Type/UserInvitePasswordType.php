<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\True;

/**
 * @TODO merge with UserNewType
 */
class UserInvitePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'password',
            'repeated',
            array(
                'first_name' => 'Password',
                'second_name' => 'Retype',
                'type' => 'password',
                'error_bubbling' => true,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => 'invite_short',
                            'message' => 'error.user.password.empty',
                        )
                    ),
                )
            )
        );

        $builder->add(
            'date_of_birth',
            'date',
            array(
                'error_bubbling' => true,
                'label' => 'Date of Birth',
                'format' => 'MMddyyyy',
                'years' => range(date('Y') - 110, date('Y')),
//                'empty_value' => array( // TODO fix validation problem (error message processing)
//                    'year' => 'Year',
//                    'month' => 'Month',
//                    'day' => 'Day',
//                ),
            )
        );

        $builder->add(
            'tos',
            'checkbox',
            array(
                'error_bubbling' => true,
                'label' => '',
                'data' => false,
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
                'cascade_validation' => true,
                'data_class' => 'CreditJeeves\DataBundle\Entity\User',
                'validation_groups' => array(
                    'invite_short',
                    'registration_tos'
                ),
            )
        );
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_newpasswordtype';
    }
}
