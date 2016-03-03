<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeaseFinderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', ['attr' => ['placeholder' => 'Name']])
            ->add('email', 'text', ['attr' => ['placeholder' => 'Email']])
            ->add('address', 'text', ['attr' => ['placeholder' => 'Address']])
            ->add('unit', 'text', ['attr' => ['placeholder' => 'Unit']]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lease_finder';
    }
}
