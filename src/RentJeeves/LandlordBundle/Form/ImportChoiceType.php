<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImportChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'select',
            'choice',
            array(
                'choices'   => array(
                    'auto'         => 'auto.import',
                    'manually'     => 'manually.import',
                ),
                'error_bubbling' => false,
                'mapped'         => false,
                'attr'           => array(
                    'class' => 'original'
                ),
                'constraints'    => array(
                    new NotBlank(),
                ),
                'expanded'      => true,
                'multiple'      => false,
            )
        );
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
        return 'import_choice_type';
    }
}
