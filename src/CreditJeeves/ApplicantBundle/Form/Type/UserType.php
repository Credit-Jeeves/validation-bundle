<?php

namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name', 'text', array('label' => 'Name'));
        $builder->add('last_name', 'text', array('label' => ''));
        //$builder->add('email', 'text', array('label' => 'Email'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CreditJeeves\DataBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'creditjeeves_applicantbundle_usertype';
    }
}
