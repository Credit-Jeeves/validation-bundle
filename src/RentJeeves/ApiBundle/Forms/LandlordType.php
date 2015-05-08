<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\PublicBundle\Form\LandlordType as Base;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;

class LandlordType extends Base
{
    const NAME = 'landlord';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'RentJeeves\DataBundle\Entity\Landlord',
            'validation_groups' => [
                'invitationApi',
            ],
            'csrf_protection' => false,
            'cascade_validation' => true,
            'inviteEmail'        => true
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
