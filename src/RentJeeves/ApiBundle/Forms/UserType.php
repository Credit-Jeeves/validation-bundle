<?php

namespace RentJeeves\ApiBundle\Forms;

use CreditJeeves\DataBundle\Enum\UserType as UserTypeEnum;
use RentJeeves\DataBundle\Validators\LandlordEmail;
use RentJeeves\DataBundle\Validators\TenantEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
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
                new TenantEmail(['groups' => 'tenant_type']),
                new LandlordEmail(['groups' => 'landlord_type'])
            ]
        ]);

        $builder->add(
            'password',
            null,
            [
                'constraints' => new NotBlank(['message' => 'api.errors.user.password_required'])
            ]
        );
    }

    public function getName()
    {
        return static::NAME;
    }
}
