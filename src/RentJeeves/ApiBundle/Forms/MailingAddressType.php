<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;

class MailingAddressType extends AbstractType
{
    const NAME = 'mailing_address';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('payee_name', 'text', ['property_path' => 'addressee']);
        $builder->add('street_address_1', 'text', ['property_path' => 'address1']);
        $builder->add('street_address_2', 'text', ['property_path' => 'address2']);
        $builder->add('state');
        $builder->add('city');
        $builder->add('zip');
        $builder->add('location_id', 'text', ['property_path' => 'locationId']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'csrf_protection' => false,
            'cascade_validation' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
