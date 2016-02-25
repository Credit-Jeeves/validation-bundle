<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use RentJeeves\CheckoutBundle\Constraint as CheckoutAssert;
use RentJeeves\CheckoutBundle\Constraint\StartDateValidator;
use RentJeeves\CheckoutBundle\Form\DataTransformer\DateTimeToStringTransformer;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorInterface;
use RentJeeves\CoreBundle\Form\Type\ViewHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentType extends AbstractType
{
    const NAME = 'rentjeeves_checkoutbundle_paymenttype';

    /**
     * @param FormBuilder $builder
     * @param array $options
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        /** @var AttributeGeneratorInterface $attributes */
        $attributes = $options['attributes'];

        $builder->add(
            'amount',
            null,
            [
                'required' => false,
                'label' => 'checkout.amount',
                'attr' => $attributes->amountAttrs(),
                'invalid_message' => 'checkout.error.amount.valid'
            ]
        );

        $builder->add(
            $builder->create(
                'paidFor',
                'choice',
                [
                    'label' => 'paidFor',
                    'choices' => $options['paid_for'],
                    'attr' => $attributes->paidForAttrs()
                ]
            )->addModelTransformer(new DateTimeToStringTransformer())
        );

        $builder->add(
            'amountOther',
            'number',
            [
                'mapped' => false,
                'required' => false,
                'label' => 'checkout.amountOther',
                'attr' => $attributes->amountOtherAttrs(),
                'constraints'     => [
                    new  Assert\Range([
                        'min' => 0,
                        'minMessage' => 'checkout.error.amountOther.min',
                        'invalidMessage' => 'checkout.error.amountOther.valid'
                    ]),
                    new Assert\Type([
                        'type' => 'numeric',
                        'message' => 'checkout.error.amountOther.valid'
                    ])
                ]
            ]
        );

        if ($attributes->isMobile()) {
            $builder->add(
                'paymentAccount',
                'choice',
                [
                    'choices' => [],
                    'attr' => $attributes->paymentAccountAttrs(),
                    'required' => false,
                ]
            );
        }

        $builder->add(
            'total',
            new ViewHiddenType(),
            [
                'label' => 'checkout.total',
                'required' => true,
                'attr' => $attributes->totalAttrs()
            ]
        );

        $builder->add(
            'type',
            'choice',
            [
                'label' => 'checkout.type',
                'empty_data' => PaymentTypeEnum::RECURRING,
                'choices' => [
                    PaymentTypeEnum::RECURRING => 'checkout.type.recurring',
                    PaymentTypeEnum::ONE_TIME => 'checkout.type.one_time',
//                    PaymentTypeEnum::IMMEDIATE => 'checkout.type.immediate', // TODO Implement
                ],
                'attr' => $attributes->typeAttrs(),
                'invalid_message' => 'checkout.error.type.invalid',
            ]
        );

        $builder->add(
            'frequency',
            'choice',
            [
                'mapped' => false,
                'label' => 'checkout.frequency',
                'empty_data' => 'monthly',
                'choices' => [
                    'monthly' => 'checkout.frequency.monthly',
                    'month_last_date' => 'checkout.frequency.month_last_date'
                ],
                'attr' => $attributes->frequencyAttrs(),
                'invalid_message' => 'checkout.error.frequency.invalid',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'checkout.error.frequency.empty',
                    ]),
                ]
            ]
        );

        $builder->add(
            'dueDate',
            'choice',
            [
                'label' => false,
                'choices' => $options['due_days'],
                'attr' => $attributes->dueDateAttrs(),
                'invalid_message' => 'checkout.error.dueDate.invalid',
                'invalid_message_parameters' => [
                    '%OPEN_DATE%' => current($options['due_days']),
                    '%CLOSE_DATE%' => end($options['due_days'])
                ]
            ]
        );

        $builder->add(
            'startMonth',
            'choice',
            [
                'label' => 'checkout.first_day',
                'choices' => $options['months'],
                'attr' => $attributes->startMonthAttrs(),
                'invalid_message' => 'checkout.error.startMonth.invalid',
            ]
        );

        $builder->add(
            'startYear',
            'choice',
            [
                'label' => false,
                'choices' => $options['years'],
                'attr' => $attributes->startYearAttrs(),
                'invalid_message' => 'checkout.error.startYear.invalid',
            ]
        );

        $builder->add(
            'start_date',
            'date',
            [
                'mapped' => false,
                'label' => 'checkout.date',
                'input' => 'string',
                'widget' => 'single_text',
                'format' => 'MM/dd/yyyy',
                'empty_data' => '',
                'attr' => $attributes->startDateAttrs(
                    StartDateValidator::isPastCutoffTime(new \DateTime(), $options['one_time_until_value'])
                ),
                'invalid_message' => 'checkout.error.date.valid',
                'constraints' => [
                    new Assert\Date([
                        'groups' => ['one_time'],
                        'message' => 'checkout.error.date.valid',
                    ]),
                    new CheckoutAssert\StartDate([
                        'groups' => [
                            'recurring',
                            'one_time'
                        ],
                        'oneTimeUntilValue' => $options['one_time_until_value'],
                        'minDate' => isset($options['min_start_date']) ? $options['min_start_date'] : null
                    ]),
                    new CheckoutAssert\DayRange([
                        'groups' => [
                            'recurring',
                            'one_time'
                        ],
                        'openDay' => $options['open_day'],
                        'closeDay' => $options['close_day'],
                    ]),
                ]
            ]
        );

        $builder->add(
            'ends',
            'choice',
            [
                'mapped' => false,
                'label' => 'checkout.ends',
                'expanded' => true,
                'empty_data' => 'cancelled',
                'choices' => [
                    'cancelled' => 'checkout.when_cancelled',
                    'on' => 'checkout.on'
                ],
                'empty_value'  => false,
                'attr' => $attributes->endsAttrs(),
                'constraints' => [
                    new Assert\NotBlank([
                            'message' => 'checkout.error.ends.empty',
                    ])
                ]
            ]
        );

        $builder->add(
            'endMonth',
            'choice',
            [
                'label' => false,
                'choices' => $options['months'],
                'attr' => $attributes->endMonthAttrs(),
                'invalid_message' => 'checkout.error.endMonth.invalid',
            ]
        );

        $builder->add(
            'endYear',
            'choice',
            [
                'label' => false,
                'choices' => $options['years'],
                'attr' => $attributes->endYearAttrs(),
                'invalid_message' => 'checkout.error.endYear.invalid',
            ]
        );

        $builder->add('next', 'submit', ['attr' => $attributes->submitAttrs()]);

        $builder->add(
            'paymentAccountId',
            'hidden',
            [
                'mapped' => false,
                'attr' => $attributes->paymentAccountIdAttrs()
            ]
        );

        $builder->add(
            'contractId',
            'hidden',
            [
                'mapped' => false,
                'attr' => $attributes->contractIdAttrs()
            ]
        );

        $builder->add(
            'id',
            'hidden',
            [
                'attr' => $attributes->idAttrs()
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $months = [];
        foreach (range(1, 12) as $month) {
            $months[$month] = date('M', strtotime("2000-{$month}-1"));
        }
        $years = range(date('Y'), date('Y') + 12);

        $resolver->setDefaults([
            'cascade_validation' => true,
            'data_class' => 'RentJeeves\DataBundle\Entity\Payment',
            'months' => $months,
            'paid_for' => [],
            'due_days' => [],
            'open_day' => 0,
            'close_day' => 0,
            'years' => array_combine($years, $years),
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $groups = ['Default'];
                if ('on' == $form->get('ends')->getData() && PaymentTypeEnum::ONE_TIME != $data->getType()) {
                    $groups[] = 'cancelled_on';
                }

                $groups[] = $data->getType();

                return $groups;
            }
        ]);

        $resolver->setRequired([
            'one_time_until_value',
            'attributes'
        ]);

        $resolver->setOptional(['min_start_date']);

        $resolver->setAllowedTypes([
            'paid_for' => 'array',
            'due_days' => 'array',
            'open_day' => 'int',
            'close_day' => 'int',
            'attributes' => 'RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorInterface',
            'min_start_date' => 'DateTime',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }
}
