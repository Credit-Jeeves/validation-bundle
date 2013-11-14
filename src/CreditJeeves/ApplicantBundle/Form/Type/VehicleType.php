<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CreditJeeves\DataBundle\Utility\VehicleUtility;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $index =  isset($options['attr']['index']) ? $options['attr']['index'] : 0;
        $vehicles = VehicleUtility::getVehicles();
        $makes = array_keys($vehicles);
        $make = $makes[$index];
        $models = array_keys($vehicles[$make]);
        $builder->add(
            'make',
            'choice',
            array(
               'choices' => array_keys($vehicles)
            )
        );
        $builder->add(
            'model',
            'choice',
            array(
                'choices' => $models
            )
        );
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'intention' => 'username',
            )
        );
    }
    
    public function getName()
    {
        return 'creditjeeves_corebundle_vehicletype';
    }
}
