<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class PropertyType extends AbstractType
{
    const NAME = '';

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('unit_name', 'text', [
            'mapped' => false
        ]);

        $builder->add('street', 'text', [
            'constraints'   => [
                new NotBlank(
                    [
                        'message'   => 'api.errors.property.street.empty',
                        'groups'    => 'new_unit'
                    ]
                )
            ]
        ]);

        $builder->add('number', 'text', [
            'constraints'   => [
                new NotBlank(
                    [
                        'message'   => 'api.errors.property.number.empty',
                        'groups'    => 'new_unit'
                    ]
                )
            ]
        ]);

        $builder->add('state', 'text', [
            'property_path' => 'area',
            'constraints'   => [
                new NotBlank(
                    [
                        'message'   => 'api.errors.property.state.empty',
                        'groups'    => 'new_unit'
                    ]
                )
            ]
        ]);

        $builder->add('city', 'text', [
            'constraints'   => [
                new NotBlank(
                    [
                        'message'   => 'api.errors.property.city.empty',
                        'groups'    => 'new_unit'
                    ]
                )
            ]
        ]);

        $builder->add('zip', 'text', [
            'constraints'   => [
                new NotBlank(
                    [
                        'message'   => 'api.errors.property.zip.empty',
                        'groups'    => 'new_unit'
                    ]
                )
            ]
        ]);

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
