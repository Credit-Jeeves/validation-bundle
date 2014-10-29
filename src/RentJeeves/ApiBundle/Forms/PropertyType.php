<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;

class PropertyType extends AbstractType
{
    const NAME = '';

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('street');

        $builder->add('number');

        $builder->add('state', 'text', [
            'property_path' => 'area'
        ]);

        $builder->add('city');

        $builder->add('zip');

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $submittedData = $event->getData();
            if (isset($submittedData['street'])) {
                if (preg_match('/^([1-9][^\s]*)\s(.+)$/s', $submittedData['street'], $array)) {
                    $submittedData['number'] = $array[1];
                    $submittedData['street'] = $array[2];
                }
            }

            $event->setData($submittedData);
        });
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'RentJeeves\DataBundle\Entity\Property',
            'cascade_validation' => true,
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return self::NAME;
    }
}
