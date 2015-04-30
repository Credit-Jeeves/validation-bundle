<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\FormBuilderInterface as FormBuilder;

class TenantDetailsType extends TenantType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('type');

        $builder->add('middle_name', 'text', ['property_path' => 'middle_initial']);

        $builder->add('phone');

        $builder->add('date_of_birth', 'birthday', [
            'widget' => 'single_text',
        ]);
    }
}
