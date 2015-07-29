<?php

namespace RentJeeves\OAuthServerBundle\Form;

use RentJeeves\DataBundle\Validators\TenantEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use Symfony\Component\Validator\Constraints\True;

class TenantType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $emailOptions = [];

        if ($options['inviteEmail']) {
            $emailOptions['constraints'] =  new TenantEmail([
                'groups'    => 'registration_tos'
            ]);
        }

        $builder->add('first_name');

        $builder->add('last_name');

        $builder->add('email', 'email', $emailOptions);

        $builder->add(
            'password',
            'repeated',
            [
                'first_name'    => 'Password',
                'second_name'   => 'Verify_Password',
                'type'          => 'password',
                'invalid_message' => 'error.user.password.match',
                'required' => true,
            ]
        );

        $builder->add(
            'tos',
            'checkbox',
            [
                'label'         => false,
                'data'          => false,
                'mapped'        => false,
                'constraints'    => new True(
                    [
                        'message'   => 'error.user.tos',
                        'groups'    => 'registration_tos'
                    ]
                ),
            ]
        );
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'RentJeeves\DataBundle\Entity\Tenant',
                'validation_groups'  => array(
                    'registration_tos',
                    'password'
                ),
                'csrf_protection'    => true,
                'csrf_field_name'    => '_token',
                'cascade_validation' => true,
                'inviteEmail'        => true
            )
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'api_tenant';
    }
}
