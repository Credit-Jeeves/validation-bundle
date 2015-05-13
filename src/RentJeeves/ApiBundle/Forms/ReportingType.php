<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\ApiBundle\Forms\DataTransformer\ReportingEnableTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use RentJeeves\ApiBundle\Forms\Enum\ReportingType as Enum;

class ReportingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->addModelTransformer(new ReportingEnableTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                Enum::ENABLED => 'enabled',
                Enum::DISABLED => 'disabled',
            ],
            'data' => Enum::DISABLED,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'reporting';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }
}
