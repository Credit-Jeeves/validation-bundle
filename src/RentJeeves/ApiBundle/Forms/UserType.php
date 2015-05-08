<?php

namespace RentJeeves\ApiBundle\Forms;

use CreditJeeves\DataBundle\Enum\UserType as UserTypeEnum;
use RentJeeves\ApiBundle\Validator\ApiTenantEmail;
use RentJeeves\DataBundle\Validators\LandlordEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    const NAME = '';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add(
            'type',
            'choice',
            [
                'choices' => [
                    UserTypeEnum::TENANT => UserTypeEnum::TENANT,
                    UserTypeEnum::LANDLORD => UserTypeEnum::LANDLORD,
                ],
            ]
        );

        $builder->add('first_name');

        $builder->add('last_name');

        if ($options['method'] != 'PUT') {
            $builder->add('email', null, [
                'constraints' =>  [
                    new ApiTenantEmail(['groups' => 'api_tenant_type_new']),
                    new LandlordEmail(['groups' => 'landlord_type'])
                ]
            ]);
        } else {
            $builder->add('email', null, [
                'mapped' => false,
            ]);
        }

        if ($options['method'] != 'PUT') {
            $builder->add(
                'password',
                'password',
                [
                    'property_path' => 'plainPassword',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'api.errors.user.password_required',
                            'groups' => 'api_new'
                        ]),
                        new Length([
                            'min' => 11,
                            'groups' => 'api'
                        ])
                    ]
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return static::NAME;
    }
}
