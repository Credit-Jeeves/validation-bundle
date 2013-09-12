<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use RentJeeves\DataBundle\Enum\PaymentStatus as PaymentStatusEnum;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'amount',
            'text',
            array(
                'attr' => array(
                    'class' => 'half-of-right',
                    'data-bind' => 'value: amount'
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'checkout.error.amount.empty',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'type',
            'choice',
            array(
                'label' => 'checkout.type',
                'empty_data' => PaymentTypeEnum::RECURRING,
                'choices' => array(
                    PaymentTypeEnum::RECURRING => 'checkout.recurring',
                    PaymentTypeEnum::IMMEDIATE => 'checkout.recurring',
                    PaymentTypeEnum::ONE_TIME => 'checkout.recurring',
                ),
                'attr' => array(
                    'class' => 'original',
                    'html' => '<div class="tooltip-box type1 pie-el">' .
                    '<h4 data-bind="i18n: {\'DUE_DAY\': dueDay}">' .
                    'checkout.type.tooltip.title-%DUE_DAY%' .
                    '</h4>' .
                    '<p data-bind="' .
                    'i18n: {\'AMOUNT\': getAmount, \'DUE_DAY\': dueDay, \'ENDS_ON\': getLastPaymentDay}' .
                    '">' .
                    'checkout.type.tooltip.text-%AMOUNT%-%DUE_DAY%-%ENDS_ON%' .
                    '</p></div>',
                    'data-bind' => 'value: type',
                    'row_attr' => array(
                        'data-bind' => 'visible: recurring'
                    )
                )
            )
        );
        $builder->add(
            'frequency',
            'choice',
            array(
                'mapped' => false,
                'label' => 'checkout.frequency',
                'empty_data' => 'checkout.recurring',
                'attr' => array(
                    'choices' => array(
                       'monthly' => 'checkout.monthly',
                       'month_last_date' => 'checkout.monthly',
                       'onetime' => 'checkout.monthly',
                    ),
                    'class' => 'original',
                    'data-bind' => 'value: type',
                    'row_attr' => array(
                        'data-bind' => 'visible: recurring'
                    )
                )
            )
        );
        $builder->add(
            'start_date',
            'date',
            array(
                'label' => 'checkout.start_date',
                'input' => 'string',
                'widget' => 'single_text',
                'format' => 'dd/MM/yy',
                'empty_data' => '',
                'attr' => array(
                    'row_attr' => array(
//                        'style' => 'display :none;', // TODO remove, it is temporary
                    ),
                    'class' => 'datepicker-field',
                    'html' => '<div class="tooltip-box type1 pie-el">' .
                        '<h4 data-bind="i18n: {\'START\': startDate, \'SETTLE\': settle}">' .
                            'checkout.start_date.tooltip.title-%START%-%SETTLE%' .
                        '</h4>' .
                        '<p data-bind="i18n: {\'SETTLE\': settle, \'SETTLE_DAYS\': settleDays}">' .
                            'checkout.start_date.tooltip.text-%SETTLE%-%SETTLE_DAYS%' .
                        '</p></div>',
                    'data-bind' => 'value: startDate'
                ),

                'error_bubbling' => true,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'checkout.error.start_date.empty',
                        )
                    ),
                    new Date(
                        array(
                            'message' => 'checkout.error.start_date.valid',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'recurring',
            'checkbox',
            array(
                'label' => 'checkout.set_up_recurring_payment',
                'attr' => array(
                    'row_attr' => array(
//                        'style' => 'display :none;', // TODO remove, it is temporary
                    ),
                    'no_box' => true,
                    'data-bind' => 'checked: recurring'
                )
            )
        );
        $builder->add(
            'type',
            'choice',
            array(
                'label' => 'checkout.type',
                'empty_data' => 'checkout.select',
                'choices' => array('monthly' => 'checkout.monthly'),
                'attr' => array(
                    'class' => 'original',
                    'html' => '<div class="tooltip-box type1 pie-el">' .
                        '<h4 data-bind="i18n: {\'DUE_DAY\': dueDay}">' .
                            'checkout.type.tooltip.title-%DUE_DAY%' .
                        '</h4>' .
                        '<p data-bind="' .
                            'i18n: {\'AMOUNT\': getAmount, \'DUE_DAY\': dueDay, \'ENDS_ON\': getLastPaymentDay}' .
                        '">' .
                            'checkout.type.tooltip.text-%AMOUNT%-%DUE_DAY%-%ENDS_ON%' .
                        '</p></div>',
                    'data-bind' => 'value: type',
                    'row_attr' => array(
                        'data-bind' => 'visible: recurring'
                    )
                )
            )
        );
        $builder->add(
            'ends',
            'choice',
            array(
                'label' => 'checkout.ends',
                'expanded' => true,
                'choices' => array('cancelled' => 'checkout.when_cancelled', 'on' => 'checkout.on'),
                'empty_value'  => false,
                'attr' => array(
                    'data-bind' => 'checked: ends',
                    'row_attr' => array(
                        'data-bind' => 'visible: recurring'
                    )
                )
            )
        );
        $builder->add(
            'ends_on',
            'date',
            array(
                'label' => false,
                'input' => 'string',
                'widget' => 'single_text',
                'format' => 'dd/MM/yy',
                'error_bubbling' => true,
                'required'  => false,
                'attr' => array(
                    'class' => 'datepicker-field',
                    'data-bind' => 'value: endsOn',
                    'box_attr' => array(
                        'data-bind' => 'visible: \'on\'==ends()'
                    )
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Payment',
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_checkoutbundle_paymenttype';
    }
}
