<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class InviteLandlordType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('unit');

        $builder->add('property');

        $builder->add('first_name');

        $builder->add('last_name');

        $builder->add('phone');

        $builder->add('email', 'email', [
            'required' => true,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $submittedData = $event->getData();
            if (isset($submittedData['unit_id'])) {
                $submittedData['unit'] = $submittedData['unit_id'];
                unset($submittedData['unit_id']);
            }
            if (isset($submittedData['property_id'])) {
                $submittedData['property'] = $submittedData['property_id'];
                unset($submittedData['property_id']);
            }
            $event->setData($submittedData);
        });
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
                'data_class' => 'RentJeeves\DataBundle\Entity\Invite',
                'validation_groups' => [
                    'inviteByApi',
                ],
            ]
        );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return '';
    }
}
