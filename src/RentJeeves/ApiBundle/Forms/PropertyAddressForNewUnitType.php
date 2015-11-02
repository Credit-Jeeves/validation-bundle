<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\ApiBundle\Forms\DataTransformer\UnitNameTransformer;
use RentJeeves\ApiBundle\Forms\DataTransformer\StreetTransformerListener;
use RentJeeves\DataBundle\Entity\Unit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class PropertyAddressForNewUnitType extends AbstractType
{
    const NAME = '';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add($builder->create('unit_name', 'text', [
            'mapped' => false,
            'constraints' => [
                new NotNull([
                    'message'   => 'api.errors.property.unit_name.specify',
                    'groups'    => ['new_unit']
                ])
            ],
            'empty_data' => null
        ])->addViewTransformer(new UnitNameTransformer()));

        $builder->add('street', 'text', [
            'constraints'   => [
                new NotBlank([
                    'message'   => 'api.errors.property.street.empty',
                    'groups'    => ['new_unit']
                ])
            ]
        ]);

        $builder->add('number', 'text', [
            'constraints'   => [
                new NotBlank([
                    'message'   => 'api.errors.property.number.empty',
                    'groups'    => ['new_unit']
                ])
            ]
        ]);

        $builder->add('state', 'text', [
            'property_path' => 'state',
            'constraints'   => [
                new NotBlank([
                    'message'   => 'api.errors.property.state.empty',
                    'groups'    => ['new_unit']
                ])
            ]
        ]);

        $builder->add('city', 'text', [
            'constraints'   => [
                new NotBlank([
                    'message'   => 'api.errors.property.city.empty',
                    'groups'    => ['new_unit']
                ])
            ]
        ]);

        $builder->add('zip', 'text', [
            'constraints'   => [
                new NotBlank([
                    'message'   => 'api.errors.property.zip.empty',
                    'groups'    => ['new_unit']
                ])
            ]
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $submittedData = $event->getData();

            if (isset($submittedData['unit_name']) && $submittedData['unit_name'] === '') {
                $submittedData['unit_name'] = Unit::SINGLE_PROPERTY_UNIT_NAME;
            }

            $event->setData($submittedData);
        });

        $builder->addEventSubscriber(new StreetTransformerListener());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'RentJeeves\DataBundle\Entity\PropertyAddress',
            'cascade_validation' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
