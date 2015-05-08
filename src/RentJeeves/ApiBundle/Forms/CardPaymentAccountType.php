<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;

class CardPaymentAccountType extends AbstractType
{
    const NAME = 'card';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('account');

        $builder->add('expiration');

        $builder->add('billing_address', new UserAddressType('card'), [
            'mapped' => false,
        ]);

        $builder->add('cvv');

        // this is hook for mapping billing_address to address field on parent form entity
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            if ($form->getParent() && $form->getParent()->getData()) {
                if (!$form->getData()) {
                    $dataClass = $form->getConfig()->getDataClass();
                    $form->setData(new $dataClass());
                }

                $form->getData()->setParent($form->getParent()->getData());

                $form->add('billing_address', new UserAddressType('card'), [
                    'mapped' => true,
                    'property_path' => 'parent.address'
                ]);
            }
        });

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'cascade_validation' => true,
            'data_class' => 'RentJeeves\ApiBundle\Forms\Entity\Card',
            'auto_initialize' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
