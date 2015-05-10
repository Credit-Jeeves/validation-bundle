<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentAccountType extends AbstractType
{
    const NAME = '';

    /**
     * @var Tenant
     */
    protected $tenant;

    /**
     * @param Tenant $tenant
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('contract_url', 'entity', [
            'mapped' => false,
            'required' => true,
            'class' => 'RentJeeves\DataBundle\Entity\Contract',
            'query_builder' => function (ContractRepository $er) {
                return $er->createQueryBuilder('c')
                    ->andWhere('c.tenant = :tenant')
                    ->setParameter(':tenant', $this->tenant);
            },
        ]);

        $builder->add(
            'type',
            'choice',
            [
                'choices' => [
                    PaymentAccountTypeEnum::BANK => 'bank',
                    PaymentAccountTypeEnum::CARD => 'card'
                ],
            ]
        );

        $builder->add('nickname', 'text', [
            'property_path' => 'name',
            'constraints'   => [
                new NotBlank([
                    'groups' => ['bank', 'card']
                ])
            ]
        ]);

        $builder->add('name', 'text', [
            'mapped' => false,
            'property_path' => 'signer_name', // added for mapping errors
            'constraints'   => [
                new NotBlank([
                    'groups' => ['bank', 'card']
                ])
            ]
        ]);

        $builder->add('bank', new BankPaymentAccountType(), [
            'mapped' => false,
        ]);

        $builder->add('card', new CardPaymentAccountType(), [
            'mapped' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'csrf_protection' => false,
                'data_class' => 'RentJeeves\DataBundle\Entity\PaymentAccount',
                'cascade_validation' => true,
                'validation_groups' => function (FormInterface $form) {
                    $data = $form->getData();
                    $type = $data->getType();
                    $groups[] = $type;

                    if (PaymentAccountTypeEnum::CARD == $type) {
                        $groups[] = 'user_address_new';
                    }

                    return $groups;
                }
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
