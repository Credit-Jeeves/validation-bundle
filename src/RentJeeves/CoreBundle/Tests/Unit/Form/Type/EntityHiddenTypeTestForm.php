<?php

namespace RentJeeves\CoreBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityHiddenTypeTestForm extends AbstractType
{
    public function getName()
    {
        return 'test_form';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'entity',
            'entity_hidden',
            [
                'mapped' => false,
                'class' => $options['class'],
            ]
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['class']);
    }
}
