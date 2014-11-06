<?php

namespace RentJeeves\ApiBundle\Forms;

use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;

class TenantType extends UserType
{
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
            'cascade_validation' => true,
            'validation_groups' => [
                'api',
                'tenant_type'
            ]
        ]);
    }
}
