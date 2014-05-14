<?php

namespace RentJeeves\PublicBundle\Form;

use RentJeeves\DataBundle\Validators\SinglePropertyConstraint as SingleProperty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PropertyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'propertyId',
            'hidden'
        );

        $builder->add(
            'units',
            'collection',
            array(
                'type'          => 'text',
                'required'      => false,
                'allow_add'     => true,
                'error_bubbling' => false,
                'options'       => array(
                    'required'  => false,
                    'attr'      => array('class' => 'unit-name'),
                )
            )
        );

        $builder->add(
            'isSingleProperty',
            'checkbox',
            array(
                'label'         => 'property.single.checkbox_label',
                'required'      => false,
                'constraints'   => new SingleProperty(),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'cascade_validation' => true
            )
        );
    }

    public function getName()
    {
        return 'propertyType';
    }
}
