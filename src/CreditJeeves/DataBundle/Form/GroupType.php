<?php

namespace CreditJeeves\DataBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type')
            ->add('name')
            ->add('code')
            ->add('website_url')
            ->add('logo_url')
            ->add('phone')
            ->add('fax')
            ->add('street_address_1')
            ->add('street_address_2')
            ->add('city')
            ->add('state')
            ->add('zip')
            ->add('description')
            ->add('group_dealers');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'CreditJeeves\DataBundle\Entity\Group'));
    }

    public function getName()
    {
        return 'creditjeeves_databundle_grouptype';
    }
}
