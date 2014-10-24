<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class BankPaymentAccountType extends AbstractType
{
    const NAME = 'bank';

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('routing');
        $builder->add('account');
        $builder->add('type');
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'csrf_protection' => false,
                'cascade_validation' => true,
                'data_class' => 'RentJeeves\ApiBundle\Forms\Entity\Bank'
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return static::NAME;
    }
}
