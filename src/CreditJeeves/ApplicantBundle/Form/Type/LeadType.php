<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use CreditJeeves\ApplicantBundle\Form\Type\GroupType;
use CreditJeeves\ApplicantBundle\Form\Type\UserType;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\CodeToGroupTransformer;
use CreditJeeves\ApplicantBundle\Form\DataTransformer\EmailToUserTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadType extends AbstractType
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
                    )
                )->addModelTransformer($groupTransformer));
        $builder->add(
            $builder->create(
                'email',
                'email',
                array(
                    'property_path' => 'user'
                    )
                )->addModelTransformer($userTransformer));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\Lead',
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'intention' => 'username',
            )
        );

        $resolver->setRequired(array(
                'em',
        ));

        $resolver->setAllowedTypes(array(
                'em' => 'Doctrine\Common\Persistence\ObjectManager',
        ));
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_leadtype';
    }
}
