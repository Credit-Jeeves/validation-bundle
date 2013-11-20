<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use CreditJeeves\ApplicantBundle\Form\Type\GroupType;
use CreditJeeves\ApplicantBundle\Form\Type\UserNewType;
use CreditJeeves\ApplicantBundle\Form\Type\TosType;
use CreditJeeves\ApplicantBundle\Form\Type\VehicleType;
use CreditJeeves\ApplicantBundle\Form\Type\NewPasswordType;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\CodeToGroupTransformer;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\EmailToUserTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class LeadNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $options['em'];
        $index = isset($options['attr']['index']) ? $options['attr']['index'] : 0;
        $groupTransformer = new CodeToGroupTransformer($entityManager);
        $builder->add(
            'user',
            new UserNewType(),
            array(
                'error_bubbling' => true,
            )
        );
        $builder->add(
            $builder->create(
                'code',
                'text',
                array(
                    'property_path' => 'group',
                    'error_bubbling' => true,
                    'label' => 'Dealer Code',
                    'constraints' => array(
                        new NotBlank(
                            array(
                                'groups' => 'user_profile',
                                'message' => 'error.group.code.empty',
                            )
                        )
                    )
                )
            )->addModelTransformer($groupTransformer)
        );
        $builder->add(
            'target_name',
            new VehicleType(),
            array(
                'error_bubbling' => true,
                'label' => 'leads.select.target',
                'attr' => array(
                    'index' => $index
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\Lead',
                'validation_groups' => array(
                    'user_profile',
                    'user_address_new'
                ),
                'cascade_validation' => true,
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'intention' => 'username',
            )
        );

        $resolver->setRequired(array('em'));

        $resolver->setAllowedTypes(array('em' => 'Doctrine\Common\Persistence\ObjectManager'));
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_leadnewtype';
    }
}
