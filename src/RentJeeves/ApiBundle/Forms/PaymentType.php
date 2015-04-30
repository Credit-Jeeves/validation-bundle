<?php

namespace RentJeeves\ApiBundle\Forms;

use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\PaymentAccountRepository;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;

class PaymentType extends AbstractType
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
        if ($options['method'] != 'PUT') {
            $builder->add('contract_url', 'entity', [
                'property_path' => 'contract',
                'class' => 'RentJeeves\DataBundle\Entity\Contract',
                'query_builder' => function (ContractRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.tenant = :tenant')
                        ->setParameter(':tenant', $this->tenant);
                },
            ]);
        }

        $builder->add('payment_account_url', 'entity', [
            'property_path' => 'payment_account',
            'class' => 'RentJeeves\DataBundle\Entity\PaymentAccount',
            'query_builder' => function (PaymentAccountRepository $er) {
                return $er->createQueryBuilder('pa')
                    ->andWhere('pa.user = :user')
                    ->setParameter(':user', $this->tenant);
            },
        ]);

        $builder->add(
            'type',
            'choice',
            [
                'choices' => [
                    PaymentTypeEnum::ONE_TIME => 'one_time',
                    PaymentTypeEnum::RECURRING => 'recurring'
                ],
            ]
        );

        $builder->add('rent', 'number', [
            'property_path' => 'amount',
            'precision' => '2'
        ]);

        $builder->add('other', 'number', [
            'precision' => '2'
        ]);

        $builder->add('day', 'integer', [
            'property_path' => 'dueDate',
        ]);

        $builder->add('month', 'integer', [
            'property_path' => 'startMonth',
        ]);

        $builder->add('year', 'integer', [
            'property_path' => 'startYear',
        ]);

        $builder->add('end_month', 'integer', [
            'property_path' => 'endMonth'
        ]);

        $builder->add('end_year', 'integer', [
            'property_path' => 'endYear'
        ]);

        $builder->add('paid_for', 'text', [
            'property_path' => 'paidForApi'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'RentJeeves\DataBundle\Entity\Payment',
            'validation_groups' => ['api', 'Default'],
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
