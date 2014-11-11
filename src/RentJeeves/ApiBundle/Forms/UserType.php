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

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

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

        $builder->add('email', null, [
            'constraints' =>  [
                new ApiTenantEmail(['groups' => 'tenant_type']),
                new LandlordEmail(['groups' => 'landlord_type'])
            ]
        ]);

        $builder->add(
            'password',
            'password',
            [
                'property_path' => 'plainPassword',
                'constraints' => [
                    new NotBlank(['message' => 'api.errors.user.password_required', 'groups' => 'api']),
                    new Length([
                        'min' => 11,
                        'groups' => 'api'
                    ])
                ]
            ]
        );
    }

    public function getName()
    {
        return static::NAME;
    }
}
