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
            'empty_data' => null,
            'description' => 'Set to "" for single-unit property',
        ])->addViewTransformer(new UnitNameTransformer()));

        $builder->add('street', 'text', ['description' => 'Street should include number.']);
        $builder->add('number', 'text', ['description' => 'Set number to street field.']);
        $builder->add('state', 'text');
        $builder->add('city', 'text');
        $builder->add('zip', 'text');

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
