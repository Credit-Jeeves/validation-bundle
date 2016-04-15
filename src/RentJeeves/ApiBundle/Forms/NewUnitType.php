<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;

class NewUnitType extends AbstractType
{
    const NAME = 'new_unit';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('address', new PropertyAddressForNewUnitType());
        $builder->add('landlord', new TrustedLandlordType());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
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
