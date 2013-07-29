<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use CreditJeeves\DataBundle\Form\ChoiceList\StateChoiceList;
use CreditJeeves\ApplicantBundle\Form\Type\SsnType;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\SsnToPartsTransformer;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            'text',
            array(
                'error_bubbling' => true,
                'label' => 'Name',
            )
        );
        $builder->add(
            'middle_initial',
            'text',
            array(
                'error_bubbling' => true,
                'label' => '',
                'required' => false,
                'max_length' => 5
            )
        );
        $builder->add(
            'last_name',
            'text',
            array(
                'error_bubbling' => true,
                'label' => ''
            )
        );
        $builder->add(
            'ssn',
            new SsnType(),
            array(
                'error_bubbling' => true,
                'label' => 'SSN',
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
            'addresses',
            'collection',
            array(
                'type' => new UserAddressType(),
                'by_reference' => true,
                'allow_add' => true,
                'error_bubbling' => true,
                'empty_data' => true,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('user_address_new'),
                            'message' => 'error.user.address.empty',
                        )
                    ),
                )
            )
        );

        $builder->add(
            'phone_type',
            'choice',
            array(
                'error_bubbling' => true,
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
                'error_bubbling' => true,
                'label' => 'Phone',
                'required' => false,
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
                    'registration_tos',
                    'user_profile',
                    'user_address_new',
                ),
            )
        );
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_usertype';
    }
}
