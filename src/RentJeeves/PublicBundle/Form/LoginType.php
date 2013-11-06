<?php

namespace RentJeeves\PublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\True;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            '_username',
            null,
            array(
                'label' => 'iframe.login.email',
                'attr' => array(
                    'class' => 'iframe-input'
                )
            )
        );
        $builder->add(
            '_password',
            'password',
            array(
                'label' => 'iframe.login.password',
                'attr' => array(
                    'class' => 'iframe-input'
                )
            )
        );
        $builder->add(
            '_csrf_token',
            'hidden'
        );
        $builder->add(
            'save',
            'submit',
            array(
                'label' => 'iframe.login.submit',
                'attr' => array(
                    'class' => 'iframe-button'
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
//                'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
//                 'validation_groups' => array(
//                 ),
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return '';// 'rentjeeves_publicbundle_logintype';
    }
}
