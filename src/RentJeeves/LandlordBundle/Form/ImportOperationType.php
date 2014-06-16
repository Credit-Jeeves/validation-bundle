<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportOperationType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'amount',
            'money',
            array(
                'currency' => '',
            )
        );

        $builder->add(
            'paidFor',
            'date',
            array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\Operation',
                'validation_groups' => array(
                    'import',
                ),
                'csrf_protection'    => false, // It's sub-form and we have protection in child which use it
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_operation';
    }
}
