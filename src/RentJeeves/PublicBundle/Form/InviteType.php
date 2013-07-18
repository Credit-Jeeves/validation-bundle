<?php

namespace RentJeeves\PublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class InviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            null,
            array(
                'label' => 'Name*'
            )
        );
        $builder->add('last_name');
        $builder->add(
            'email',
            null,
            array(
                'label' => 'Email*'
            )
        );
        $builder->add('phone');
        $builder->add(
            'password',
            'repeated',
            array(
                'first_name'    => 'Password',
                'second_name'   => 'Verify_Password',
                'type'          => 'password',
                'mapped'        => false,
                'constraints'   => array(
                    new NotBlank(
                        array(
                            'groups'    => 'password',
                            'message'   => 'error.user.password.empty',
                        )
                    ),
                ),
            )
        );
        $builder->add(
            'tos',
            'checkbox',
            array(
                'label'         => '',
                'data'          => false,
                'mapped'        => false,
                'constraints'    => new True(
                    array(
                        'message'   => 'error.user.tos',
                        'groups'    => 'registration_tos'
                    )
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\Tenant',
                'validation_groups' => array(
                    'registration_tos',
                    'invite',
                    'password'
            ),
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_publicbundle_invitetype';
    }
}
