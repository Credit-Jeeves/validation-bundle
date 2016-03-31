<?php

namespace RentJeeves\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MriASIType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'holdingid',
            null,
            [
                'label' => 'Holding ID',
                'required' => true
            ]
        );

        $builder->add(
            'resid',
            null,
            [
                'label' => 'Resident ID',
                'required' => true
            ]
        );

        $builder->add(
            'leaseid',
            null,
            [
                'label' => 'Lease ID',
                'required' => true
            ]
        );

        $builder->add(
            'propid',
            null,
            [
                'label' => 'External Property ID',
                'required' => true
            ]
        );

        $builder->add(
            'buildingid',
            null,
            [
                'label' => 'External Building ID',
                'required' => false,
            ]
        );

        $builder->add(
            'unitid',
            null,
            [
                'label' => 'External Unit ID',
                'required' => false,
            ]
        );

        $builder->add(
            'rent',
            null,
            [
                'label' => 'Rent',
                'required' => false,
            ]
        );

        $builder->add(
            'appfee',
            null,
            [
                'label' => 'Application Fee',
                'required' => false,
            ]
        );

        $builder->add(
            'secdep',
            null,
            [
                'label' => 'Security Deposit',
                'required' => false,
            ]
        );

        $builder->add(
            'ReturnUrl',
            null,
            [
                'label' => 'Return Url',
                'required' => false,
            ]
        );

        $builder->add(
            'UserCancelUrl',
            null,
            [
                'label' => 'User Cancel Url',
                'required' => false,
            ]
        );

        $builder->add(
            'trackingid',
            null,
            [
                'label' => 'External Tracking ID',
                'required' => false,
            ]
        );

        $builder->add(
            'Digest',
            null,
            [
                'label' => 'Digest',
                'required' => true,
                'read_only' => true,
            ]
        );

        $builder->add('GenerateDigest',
            'button',
            [
                'attr' => [
                    'style' => 'width:150px;float:left;',
                    'force_row' => true,
                    'onClick' => 'generateDigest();'
                ],
            ]
        );

        $builder->add(
            'Submit',
            'submit',
            [
                'disabled' => true,
                'attr' => [
                    'style' => 'width:150px;float:left;'
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false
        ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string Should be empty name b/c we get params without array
     */
    public function getName()
    {
        return '';
    }
}