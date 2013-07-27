<?php
namespace CreditJeeves\UserBundle\Form\Type;

use CreditJeeves\ApplicantBundle\Form\Type\GroupType;
use CreditJeeves\ApplicantBundle\Form\Type\PasswordType;
use CreditJeeves\ApplicantBundle\Form\Type\UserType;
use CreditJeeves\ApplicantBundle\Form\Type\TosType;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\CodeToGroupTransformer;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\EmailToUserTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class LeadReturnedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['em'];
        $groupTransformer = new CodeToGroupTransformer($entityManager);
        $userTransformer = new EmailToUserTransformer($entityManager);
        $builder->add(
            'user',
            new UserType()
        );
        $builder->add(
            $builder->create(
                'code',
                'text',
                array(
                    'property_path' => 'group',
                    'label' => 'Dealer Code',
                    'error_bubbling' => true,
                    'constraints' => array(
                        new NotBlank(
                            array(
                                'groups' => 'user_profile',
                                'message' => 'error.group.code.empty'
                            )
                        )
                    )
                )
            )->addModelTransformer($groupTransformer)
        );
        $builder->add(
            $builder->create(
                'email',
                'email',
                array(
                    'error_bubbling' => true,
                    'property_path' => 'user'
                    )
            )->addModelTransformer($userTransformer)
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'CreditJeeves\DataBundle\Entity\Lead',
                'validation_groups' => array(
                        'registration_tos',
                        'user_profile',
                        'user_address_new',
                ),
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'intention' => 'username'
            )
        );

        $resolver->setRequired(array('em'));

        $resolver->setAllowedTypes(array('em' => 'Doctrine\Common\Persistence\ObjectManager'));
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_leadreturnedtype';
    }
}
