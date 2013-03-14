<?php
namespace CreditJeeves\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


class PasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password',     'password', array('label' => 'Old Password'));
        $builder->add(
            'new_password',
            'repeated',
                array(
                    'first_name' => 'Password',
                    'second_name' => 'Retype',
                    'type' => 'password',
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CreditJeeves\UserBundle\Entity\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'username',
        ));
    }    
    public function getName()
    {
        return 'password';
    }
}