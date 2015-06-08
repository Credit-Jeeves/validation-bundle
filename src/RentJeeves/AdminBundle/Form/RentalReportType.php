<?php

namespace RentJeeves\AdminBundle\Form;

use RentJeeves\CoreBundle\Report\Enum\CreditBureau;
use RentJeeves\CoreBundle\Report\Enum\RentalReportType as RentalReportTypeOptions;
use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RentalReportType extends Base
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'bureau',
            'choice',
            [
                'error_bubbling' => true,
                'label'          => 'admin.report.choose_bureau',
                'choices'        => array_map(
                    'strtoupper',
                    array_change_key_case(
                        CreditBureau::all(),
                        CASE_LOWER
                    )
                )
            ]
        );

        $builder->add(
            'type',
            'choice',
            [
                'error_bubbling' => true,
                'label'          => 'admin.report.choose_type',
                'choices'        => array_map(
                    'strtoupper',
                    array_change_key_case(
                        RentalReportTypeOptions::all(),
                        CASE_LOWER
                    )
                )
            ]
        );

        $builder->add(
            'month',
            'date',
            [
                'error_bubbling' => true,
                'label'          => 'admin.report.choose_month',
                'format'         => 'MMMMyyyydd',
                'years'          => range(date('Y'), date('Y') - 1),
                'days'           => [1]
            ]
        );

        $builder->add(
            'startDate',
            'date',
            [
                'error_bubbling' => true,
                'label'          => 'admin.report.choose_start_date',
                'widget'         => 'single_text',
            ]
        );

        $builder->add(
            'endDate',
            'date',
            [
                'error_bubbling' => true,
                'label'          => 'admin.report.choose_end_date',
                'widget'         => 'single_text',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'RentJeeves\CoreBundle\Report\RentalReportData'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'rental_report';
    }
}
