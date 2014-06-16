<?php

namespace RentJeeves\LandlordBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ViewType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                // hidden fields cannot have a required attribute
                'required'       => false,
                // Pass errors to the parent
                'error_bubbling' => true,
                'compound'       => false,
            )
        );
    }

    public function getName()
    {
        return 'view';
    }
}
