<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TenantType extends UserType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('holding_id', 'entity', [
            'class' => 'CreditJeeves\DataBundle\Entity\Holding',
            'mapped' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'api.errors.tenant.holding_id.empty',
                    'groups' => ['accounting_checked']
                ]),
            ]
        ]);

        $builder->add('resident_id', null, [
            'mapped' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'api.errors.tenant.resident_id.empty',
                    'groups' => ['accounting_checked']
                ]),
            ]
        ]);
    }

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

                    if ($form->get('holding_id')->getData() || $form->get('resident_id')->getData()) {
                        $groups[] = 'accounting_checked';
                    }
                }

                return $groups;
            }
        ]);
    }
}
