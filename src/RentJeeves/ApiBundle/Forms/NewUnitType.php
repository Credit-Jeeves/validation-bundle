<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;

class NewUnitType extends AbstractType
{
    const NAME = 'new_unit';

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('address', new PropertyType());
        $builder->add('landlord', new LandlordType());
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return static::NAME;
    }
}
