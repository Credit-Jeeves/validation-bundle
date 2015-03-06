<?php

namespace RentJeeves\PublicBundle\Form;

use RentJeeves\DataBundle\Validators\LandlordEmail;
use RentJeeves\TenantBundle\Form\DataTransformer\PhoneNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;

class LandlordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            null,
            array(
                'label' => 'Name',
            )
        );
        $builder->add('last_name');

        $emailOptions = array(
            'label' => 'Email',
        );
        if ($options['inviteEmail']) {
            $emailOptions['constraints'] =  new LandlordEmail(
                array(
                    'groups'    => 'registration_tos'
                )
            );
        }
        $builder->add(
            'email',
            null,
            $emailOptions
        );
        $builder->add(
            $builder->create('phone', 'text', ['required' => false])->addViewTransformer(new PhoneNumberTransformer())
        );
        $builder->add(
            'password',
            'repeated',
            array(
                'first_name'    => 'Password',
                'first_options' => array(
                    'label' => 'Password'
                 ),
                'second_name'   => 'Verify_Password',
                'second_options' => array(
                    'label' => 'Verify Password'
                 ),
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
                'data_class' => 'RentJeeves\DataBundle\Entity\Landlord',
                'validation_groups' => array(
                    'registration_tos',
                    'invite',
                    'password',
                ),
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'cascade_validation' => true,
                'inviteEmail'        => false
            )
        );
    }

    public function getName()
    {
        return 'landlordType';
    }
}
