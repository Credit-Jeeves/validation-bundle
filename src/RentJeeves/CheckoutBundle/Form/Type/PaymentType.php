<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Callback;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use Symfony\Component\Validator\ExecutionContextInterface;
use DateTime;

class PaymentType extends AbstractType
{
    /**
     * @var string
     */
    protected $oneTimeUntilValue;

    /**
     * @param string $oneTimeUntilValue
     */
    public function __construct($oneTimeUntilValue)
    {
        $this->oneTimeUntilValue = $oneTimeUntilValue;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'amount',
            null,
            array(
                'label' => 'checkout.amount',
                'attr' => array(
                    'min' => 1,
                    'step' => '0.01',
                    'class' => 'half-of-right',
                    'data-bind' => 'value: payment.amount'
                ),
                'invalid_message' => 'checkout.error.amount.valid'
            )
        );

        $builder->add(
            'type',
            'choice',
            array(
                'label' => 'checkout.type',
                'empty_data' => PaymentTypeEnum::RECURRING,
                'choices' => array(
                    PaymentTypeEnum::RECURRING => 'checkout.type.recurring',
                    PaymentTypeEnum::ONE_TIME => 'checkout.type.one_time',
//                    PaymentTypeEnum::IMMEDIATE => 'checkout.type.immediate', // TODO Implement
                ),
                'attr' => array(
                    'class' => 'original',
                    'html' => '<div class="tooltip-box type3 pie-el" ' .
                                    'data-bind="visible: \'recurring\' == payment.type()">' .
                        '<h4 data-bind="' .
                            'text: \'checkout.recurring.\' + payment.frequency() + \'.tooltip.title-%DUE_DAY%\', ' .
                            'i18n: {\'DUE_DAY\': payment.dueDate}' .
                        '"></h4>' .
                        '<p data-bind="' .
                            'text: \'checkout.recurring.\' + payment.frequency() + \'.\' + payment.ends() + ' .
                                '\'.tooltip.text-%AMOUNT%-%DUE_DAY%-%ENDS_ON%-%SETTLE_DAYS%\', ' .
                            'i18n: {' .
                                '\'AMOUNT\': getAmount, ' .
                                '\'DUE_DAY\': payment.dueDate, ' .
                                '\'SETTLE_DAYS\': settleDays, ' .
                                '\'ENDS_ON\': getLastPaymentDay' .
                            '}' .
                        '"></p></div>',
                    'data-bind' => 'value: payment.type',
                    'row_attr' => array(
                        'data-bind' => ''
                    )
                ),
                'invalid_message' => 'checkout.error.type.invalid',
            )
        );
        $builder->add(
            'frequency',
            'choice',
            array(
                'mapped' => false,
                'label' => 'checkout.frequency',
                'empty_data' => 'monthly',
                'choices' => array(
                    'monthly' => 'checkout.frequency.monthly',
                    'month_last_date' => 'checkout.frequency.month_last_date'
                ),
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: payment.frequency',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'recurring\' == payment.type()'
                    )
                ),
                'invalid_message' => 'checkout.error.frequency.invalid',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'checkout.error.frequency.empty',
                        )
                    ),
                )
            )
        );
        $days = range(1, 31);
        $builder->add(
            'dueDate',
            'choice',
            array(
                'label' => false,
                'choices' => array_combine($days, $days),
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: payment.dueDate',
                    'box_attr' => array(
                        'data-bind' => 'visible: \'monthly\' == payment.frequency()'
                    )
                ),
                'invalid_message' => 'checkout.error.dueDate.invalid',
            )
        );
        $months = array();
        foreach (range(1, 12) as $month) {
            $months[$month] = date('M', strtotime("2000-{$month}-1"));
        }
        $builder->add(
            'startMonth',
            'choice',
            array(
                'label' => 'checkout.first_day',
                'choices' => $months,
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: payment.startMonth',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'recurring\' == payment.type()'
                    )
                ),
                'invalid_message' => 'checkout.error.startMonth.invalid',
            )
        );
        $years = range(date('Y'), date('Y') + 12);
        $builder->add(
            'startYear',
            'choice',
            array(
                'label' => false,
                'choices' => array_combine($years, $years),
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: payment.startYear',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'recurring\' == payment.type()'
                    )
                ),
                'invalid_message' => 'checkout.error.startYear.invalid',
            )
        );
        $builder->add(
            'start_date',
            'date',
            array(
                'mapped' => false,
                'label' => 'checkout.date',
                'input' => 'string',
                'widget' => 'single_text',
                'format' => 'M/d/yyyy',
                'empty_data' => '',
                'attr' => array(
                    'class' => 'datepicker-field',
                    'readonly'=> 'true',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'one_time\' == payment.type()'
                    ),
                    'data-bind' => 'value: payment.startDate',
                    'html' => '<div class="tooltip-box type3 pie-el">' .
                            '<h4 data-bind="i18n: {\'START\': payment.startDate, \'SETTLE\': settle}">' .
                                'checkout.one_time.tooltip.title-%START%-%SETTLE%' .
                            '</h4>' .
                            '<p data-bind="i18n: {\'AMOUNT\': getAmount, \'START\': payment.startDate}">' .
                                'checkout.one_time.tooltip.text-%AMOUNT%-%START%' .
                        '</p></div>',
                ),
                'invalid_message' => 'checkout.error.date.valid',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('one_time'),
                            'message' => 'checkout.error.date.empty',
                        )
                    ),
                    new Date(
                        array(
                            'groups' => array('one_time'),
                            'message' => 'checkout.error.date.valid',
                        )
                    ),
                    new Callback(
                        array(
                            'groups' => array('one_time'),
                            'methods' => array(array($this, 'isInTime')),
                        )
                    ),
                    new Callback(
                        array(
                            'groups' => array('one_time'),
                            'methods' => array(array($this, 'isLaterOrEqualNow'))
                        )
                    ),
                )
            )
        );
        $builder->add(
            'ends',
            'choice',
            array(
                'mapped' => false,
                'label' => 'checkout.ends',
                'expanded' => true,
                'empty_data' => 'cancelled',
                'choices' => array('cancelled' => 'checkout.when_cancelled', 'on' => 'checkout.on'),
                'empty_value'  => false,
                'attr' => array(
                    'data-bind' => 'checked: payment.ends',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'recurring\' == payment.type()'
                    )
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'checkout.error.ends.empty',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'endMonth',
            'choice',
            array(
                'label' => false,
                'choices' => $months,
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: payment.endMonth',
                    'box_attr' => array(
                        'data-bind' => 'visible: \'on\' == payment.ends()'
                    )
                ),
                'invalid_message' => 'checkout.error.endMonth.invalid',
            )
        );
        $builder->add(
            'endYear',
            'choice',
            array(
                'label' => false,
                'choices' => array_combine($years, $years),
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: payment.endYear',
                    'box_attr' => array(
                        'data-bind' => 'visible: \'on\' == payment.ends()'
                    )
                ),
                'invalid_message' => 'checkout.error.endYear.invalid',
            )
        );

        $builder->add('submit', 'submit', array('attr' => array('force_row' => true, 'class' => 'hide_submit')));
        $builder->add(
            'paymentAccountId',
            'hidden',
            array(
                'mapped' => false,
                'attr' => array(
                    'data-bind' => 'value: payment.paymentAccountId',
                )
            )
        );
        $builder->add(
            'contractId',
            'hidden',
            array(
                'mapped' => false,
                'attr' => array(
                    'data-bind' => 'value: payment.contractId',
                )
            )
        );
        $builder->add(
            'id',
            'hidden',
            array(
                'attr' => array(
                    'data-bind' => 'value: payment.id',
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'RentJeeves\DataBundle\Entity\Payment',
                'validation_groups' => function (FormInterface $form) {
                    $data = $form->getData();
                    $groups = array('Default');
                    if ('on' == $form->get('ends')->getData() && PaymentTypeEnum::ONE_TIME != $data->getType()) {
                        $groups[] = 'cancelled_on';
                    }

                    $groups[] = $data->getType();

                    return $groups;
                }
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_checkoutbundle_paymenttype';
    }

    public function isInTime($data, ExecutionContextInterface $validatorContext)
    {
        $now = new DateTime();
        $until = new DateTime($this->oneTimeUntilValue);
        if ($now->format('Y-m-d') == $data && $now >= $until) {
            $validatorContext->addViolationAt('start_date', 'checkout.error.date.not_in_time', array(), null);
        }
    }

    public function isLaterOrEqualNow($data, ExecutionContextInterface $validatorContext)
    {
        $now = new DateTime();
        $now->setTime(0, 0);

        $payDate = new DateTime($data);
        if ($payDate < $now) {
            $validatorContext->addViolationAt('start_date', 'checkout.error.date.is_in_past', array(), null);
        }
    }
}
