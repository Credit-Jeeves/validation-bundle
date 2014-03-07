<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportMatchFileType extends AbstractType
{
    protected $number;

    public function __construct($number)
    {
        $this->number  = $number;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for($i = 1; $i <= $this->number; $i++) {
            $builder->add(
                'collum'.$i,
                'choice',
                array(
                    'choices'   => array(
                        'balance'       => 'Balance',
                        'resident_id'   => 'Resident ID',
                        'tenant_name'   => 'Tenant Name',
                        'rent'          => 'Rent',
                        'email'         => 'Email',
                        'end_date'      => 'Lease End',
                        'end_date'      => 'Unit',
                    ),
                    'error_bubbling' => true,
                )
            );
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection'    => true,
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'import_match_file_type';
    }
}

