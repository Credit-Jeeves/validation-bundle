<?php

namespace RentJeeves\CheckoutBundle\Form\Type;

use RentJeeves\CheckoutBundle\Constraint\DayRange;
use RentJeeves\CheckoutBundle\Constraint\StartDate;
use RentJeeves\CheckoutBundle\Constraint\StartDateValidator;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\PayAnythingAttributeGeneratorWeb as AttributeGenerator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;

class PayAnythingPaymentType extends AbstractType
{
    const NAME = 'rentjeeves_checkoutbundle_payanything_paymenttype';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add(
            'payFor',
            'choice',
            [
                'mapped'=> false,
                'label' => 'checkout.pay_anything.payFor',
                'choices' => $options['availablePayFor'],
                'attr' => AttributeGenerator::payForAttrs(),
                'constraints' => [
                    new NotBlank([
                        'message' => 'checkout.pay_anything.error.payFor.invalid',
                        'groups'  => ['pay_anything'],
                    ])
                ]
            ]
        );

        $builder->add(
            'amount',
            null,
            [
                'required' => true,
                'label' => 'checkout.pay_anything.amount',
                'attr' => AttributeGenerator::amountAttrs(),
                'invalid_message' => 'checkout.error.amount.pay_anything.invalid',
                'constraints' => [
                    new NotBlank([
                        'message' => 'checkout.error.amount.pay_anything.invalid',
                        'groups'  => ['pay_anything'],
                    ])
                ]
            ]
        );

        $builder->add(
            'start_date',
            'date',
            [
                'mapped'          => false,
                'label'           => 'checkout.date',
                'input'           => 'string',
                'widget'          => 'single_text',
                'format'          => 'MM/dd/yyyy',
                'empty_data'      => '',
                'attr'            => AttributeGenerator::startDateAttrs(
                    StartDateValidator::isPastCutoffTime(new \DateTime(), $options['oneTimeUntilValue'])
                ),
                'invalid_message' => 'checkout.error.date.valid',
                'constraints'     => [
                    new Date([
                        'groups'  => ['pay_anything'],
                        'message' => 'checkout.error.date.valid',
                    ]),
                    new StartDate([
                        'groups'  => ['pay_anything'],
                        'oneTimeUntilValue' => $options['oneTimeUntilValue'],
                    ]),
                    new DayRange([
                        'groups'  => ['pay_anything'],
                        'openDay'    => $options['openDay'],
                        'closeDay'   => $options['closeDay']
                    ]),
                ]
            ]
        );

        $builder->add(
            'dueDate',
            'hidden',
            [
                'attr' => AttributeGenerator::dueDateAttrs()
            ]
        );

        $builder->add(
            'startMonth',
            'hidden',
            [
                'attr' => AttributeGenerator::startMonthAttrs()
            ]
        );

        $builder->add(
            'startYear',
            'hidden',
            [
                'attr' => AttributeGenerator::startYearAttrs()
            ]
        );

        $builder->add('next', 'submit', ['attr' => AttributeGenerator::submitAttrs()]);

        $builder->add(
            'paymentAccountId',
            'hidden',
            [
                'mapped' => false,
                'attr' => AttributeGenerator::paymentAccountIdAttrs()
            ]
        );

        $builder->add(
            'contractId',
            'hidden',
            [
                'mapped' => false,
                'attr' => AttributeGenerator::contractIdAttrs()
            ]
        );

        $builder->add(
            'type',
            'hidden',
            [
                'data' => PaymentTypeEnum::ONE_TIME,
                'constraints' => new EqualTo([
                    'value' => PaymentTypeEnum::ONE_TIME,
                    'groups'  => ['pay_anything'],
                ]),
            ]
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['oneTimeUntilValue']);

        $resolver->setDefaults([
            'cascade_validation' => true,
            'data_class' => 'RentJeeves\DataBundle\Entity\Payment',
            'validation_groups' => ['pay_anything'],
            'availablePayFor' => [],
            'openDay' => 0,
            'closeDay' => 0,
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
