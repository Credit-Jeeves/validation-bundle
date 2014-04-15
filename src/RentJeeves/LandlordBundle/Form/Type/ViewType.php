<?php

namespace RentJeeves\LandlordBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ViewType  extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array());
    }

    public function getParent()
    {
        return 'hidden';
    }

    public function getName()
    {
        return 'view';
    }
} 