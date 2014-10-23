<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\DataBundle\Entity\ContractRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentAccountType extends AbstractType
{
    const NAME = '';

    protected $tenant;

    public function __construct($tenant)
    {
        $this->tenant = $tenant;
    }

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
            'property_path' => 'name'
        ]);

        $builder->add('name', 'text', [
            'mapped' => false
        ]);

        $builder->add('bank', new BankPaymentAccountType(), [
            'mapped' => false,
        ]);


        $builder->add('card', new CardPaymentAccountType(), [
            'mapped' => false,
        ]);
    }

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
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return static::NAME;
    }
}
