<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;

class ContractType extends AbstractType
{
    const NAME = '';

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('unit_url', 'entity', [
            'class' => 'RentJeeves\DataBundle\Entity\Unit',
            'mapped' => false
        ]);

        $builder->add('new_unit', new NewUnitType(), [
            'mapped' => false
        ]);


        $builder->add('experian_reporting', new ReportingType(), [
            'property_path' => 'reportToExperian'
        ]);
    }


    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
            'csrf_protection' => false,
            'cascade_validation' => true,
            'validation_groups' => function (FormInterface $form) {
                $unit = $form->get('unit_url')->getViewData();
                $contract = $form->getData();

                $groups = [];

                if (!$contract->getId()) {
                    if (is_null($unit) || $unit === '') {
                        $groups[] = 'new_unit';
                        $groups[] = 'invitationApi';
                    } else {
                        $groups[] = 'unit_url';
                    }
                }

                return $groups;
            }
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
