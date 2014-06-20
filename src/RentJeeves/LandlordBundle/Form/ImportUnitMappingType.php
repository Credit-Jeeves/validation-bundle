<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Form\Type\ViewType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportUnitMappingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'externalUnitId',
            new ViewType(),
            array(
                'error_bubbling' => false,
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\UnitMapping',
                'validation_groups' => array(
                    'import',
                ),
                'csrf_protection'    => false,
            )
        );
    }

    public function getName()
    {
        return 'import_unit_mapping';
    }
}
