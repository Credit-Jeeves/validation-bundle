<?php

namespace RentJeeves\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ViewHiddenType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'required'       => false,
                // Pass errors to the parent
                'error_bubbling' => true,
                'compound'       => false,
            )
        );
    }

    public function getName()
    {
        return 'view_hidden';
    }
}
