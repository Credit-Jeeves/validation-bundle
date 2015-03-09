<?php

namespace RentJeeves\PublicBundle\Form;

use RentJeeves\TenantBundle\Form\DataTransformer\PhoneNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReturnedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            null,
            array(
                'label' => 'Name*',
            )
        );
        $builder->add('last_name');
        $builder->add(
            'email',
            null,
            array(
                'label' => 'Email*',
            )
        );
        $builder->add(
            $builder->create('phone', 'text', ['required' => false])->addViewTransformer(new PhoneNumberTransformer())
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
                'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
                'validation_groups' => array(
                    'registration_tos',
                ),
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_publicbundle_returnedtype';
    }
}
