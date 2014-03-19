<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\LandlordBundle\Report\AccountingImport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * This form for exit Tenant and new Contract
 *
 * Class ImportNewUserWithContractType
 * @package RentJeeves\LandlordBundle\Form
 */
class ImportUnitType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'attr'           => array(
                    'class'     => 'half-width',
                    'data-bind' => 'value: name',
                ),
            )
        );


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => array(
                    'import_unit',
                ),
                'csrf_protection'    => true,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_unit';
    }
}

