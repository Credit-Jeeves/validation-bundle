<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

use CreditJeeves\DataBundle\Form\ChoiceList\StateChoiceList;
use CreditJeeves\ApplicantBundle\Form\Type\SsnType;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\SsnToPartsTransformer;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

/**
 * FIXME it must extends UserType form
 */
class UserNewType extends AbstractType //UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            'text',
            array(
                'label' => 'Name',
                'error_bubbling' => true,
            )
        );
        $builder->add(
            'middle_initial',
            'text',
            array(
                'label' => '',
                'required' => false,
                'max_length' => 5
                )
        );
        $builder->add(
            'last_name',
            'text',
            array(
                'label' => '',
                'error_bubbling' => true,
            )
        );
        $builder->add(
            'email',
            'email',
            array(
                'label' => 'Email',
                'error_bubbling' => true,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => 'user_profile',
                            'message' => 'error.user.email.empty',
                        )
                    ),
                    new Email(
                        array(
                            'groups' => 'user_profile',
                            'message' => 'error.user.email.error',
                        )
                    ),
                ),
            )
        );
        $builder->add(
            'ssn',
            new SsnType(),
            array(
                'label' => 'SSN',
                'error_bubbling' => true,
                )
        );
        $builder->add(
            'date_of_birth',
            'birthday',
            array(
                'label' => 'Date of Birth',
                'error_bubbling' => true,
                'format' => 'MMMddyyyy',
            )
        );
        $builder->add(
            'street_address1',
            'text',
            array(
                'label' => 'Address',
                'error_bubbling' => true,
            )
        );
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
                            'groups' => 'user_profile',
                            'message' => 'error.user.password.empty',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'unit_no',
            'text',
            array(
                'label' => '',
                'error_bubbling' => true,
            )
        );
        $builder->add(
            'city',
            'text',
            array(
                'label' => '',
                'error_bubbling' => true,
            )
        );
        $builder->add(
            'state',
            'choice',
            array(
                'label' => '',
                'error_bubbling' => true,
                'choice_list' =>  new StateChoiceList(),
                'required' => true,
            )
        );
        $builder->add(
            'zip',
            'text',
            array(
                'label' => '',
                'error_bubbling' => true,
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
                'label' => 'Phone',
                'required' => false,
                )
        );
        $builder->add(
            'tos',
            'checkbox',
            array(
                'label' => '',
                'data' => false,
                'mapped' => false,
                'error_bubbling' => true,
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
                    'registration_tos',
                    'user_profile',
                    'user_address',
                ),
            )
        );
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_usernewtype';
    }
}
