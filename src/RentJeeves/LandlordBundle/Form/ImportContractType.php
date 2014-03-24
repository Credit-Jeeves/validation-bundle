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
class ImportContractType extends AbstractType
{
    protected $token;

    public function __construct($token = true)
    {
        $this->token =  $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'startAt',
            'date',
            array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
            )
        );


        $builder->add(
            'finishAt',
            'date',
            array(
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
            )
        );

        $builder->add(
            'importedBalance',
            'text',
            array()
        );

        $builder->add(
            'rent',
            'text',
            array()
        );

        $builder->add(
            'rent',
            'text',
            array()
        );

        if ($this->token) {
            $builder->add(
                '_token',
                'hidden',
                array(
                    'mapped' => false,
                )
            );
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => array(
                    'import',
                ),
                'csrf_protection'    => false,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_contract';
    }
}

