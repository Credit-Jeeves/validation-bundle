<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;

class TenantType extends UserType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
            'cascade_validation' => true,
            'validation_groups' => function (FormInterface $form) {
                $tenant = $form->getData();
                $groups = [
                    'api',
                    'tenant_type'
                ];

                if ($tenant and $tenant instanceof Tenant and $tenant->getId()) {
                    $groups[] = 'api_update';
                } else {
                    $groups[] = 'api_new';
                    $groups[] = 'api_tenant_type_new';
                }

                return $groups;
            }
        ]);
    }
}
