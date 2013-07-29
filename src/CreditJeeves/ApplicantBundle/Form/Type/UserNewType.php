<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use CreditJeeves\ApplicantBundle\Form\DataTransformer\AddressesToAddressTransformer;
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

class UserNewType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

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
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_usernewtype';
    }
}
