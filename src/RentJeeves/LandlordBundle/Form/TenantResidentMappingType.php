<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TenantResidentMappingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'residentId',
            'text',
            [
                'error_bubbling' => true,
            ]
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\ResidentMapping',
                'validation_groups' => array(
                    'add_or_edit_tenants',
                ),
                'csrf_protection' => false,
            )
        );
    }

    public function getName()
    {
        return 'tenant_resident_mapping';
    }
}
