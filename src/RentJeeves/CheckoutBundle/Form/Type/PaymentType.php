<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use RentJeeves\CheckoutBundle\Constraint\DayRange;
use RentJeeves\CheckoutBundle\Constraint\StartDate;
use RentJeeves\CheckoutBundle\Form\DataTransformer\DateTimeToStringTransformer;
use RentJeeves\CoreBundle\Form\Type\ViewHiddenType;
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
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

class PaymentType extends AbstractType
{
    const NAME = 'rentjeeves_checkoutbundle_paymenttype';

    /**
     * @var string
     */
    protected $oneTimeUntilValue;

    /**
     * @var array
     */
    protected $paidFor = array();

    /**
     * @var array
     */
    protected $dueDays = array();

    /**
     * @var integer
     */
    protected $openDay;

    /**
     * @var integer
     */
    protected $closeDay;

    /**
     * @param string $oneTimeUntilValue
     */
    public function __construct(
        $oneTimeUntilValue,
        array $paidFor,
        $dueDays,
        $openDay,
        $closeDay
    ) {
        $this->oneTimeUntilValue = $oneTimeUntilValue;
        $this->paidFor = $paidFor;
        $this->dueDays = $dueDays;
        $this->openDay = $openDay;
        $this->closeDay = $closeDay;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'amount',
            null,
            array(
                'required' => false,
                'label' => 'checkout.amount',
                'attr' => array(
                    'min' => 1,
                    'step' => '0.01',
                    'class' => 'half-of-right',
                    'data-bind' => 'value: payment.amount',
                ),
                'invalid_message' => 'checkout.error.amount.valid'
            )
        );
        $builder->add(
            $builder->create(
                'paidFor',
                'choice',
                array(
                    'label' => 'paidFor',
                    'choices' => $this->paidFor,
                    'attr' => array(
                        'class' => 'original paid-for',
                        'data-bind' => "options: payment.paidForOptions, optionsText: 'text', optionsValue: 'value', ".
                        "value: payment.paidFor",
                        'force_row' => false,
                        'template' => 'paidFor-html',
                    ),
                    'constraints' => array(
                        new NotBlank(
                            array(
                                'message' => 'checkout.error.paidFor.invalid'
                            )
                        )
                    )
                )
            )->addModelTransformer(new DateTimeToStringTransformer())
        );

        $builder->add(
            'amountOther',
            'number',
            array(
                'mapped' => false,
                'required' => false,
                'label' => 'checkout.amountOther',
                'attr' => array(
                    'step' => '0.01',
                    'class' => 'half-of-right',
                    'data-bind' => 'value: payment.amountOther'
                ),
                'constraints'     => array(
                    new  Assert\Range(
                        array(
                            'min' => 0,
                            'minMessage' => 'checkout.error.amountOther.min',
                            'invalidMessage' => 'checkout.error.amountOther.valid'
                        )
                    ),
                    new Type(
                        array(
                            'type' => 'numeric',
                            'message' => 'checkout.error.amountOther.valid'
                        )
                    )
                )
            )
        );

        $builder->add(
            'total',
            new ViewHiddenType(),
            array(
                'label' => 'checkout.total',
                'required' => true,
                'attr' => array(
                    'data-bind' => 'value: totalInput',
                    'view' => array(
                        'data-bind' => 'text: getTotal',
                    )
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
                    PaymentTypeEnum::RECURRING => 'checkout.type.recurring',
                    PaymentTypeEnum::ONE_TIME => 'checkout.type.one_time',
//                    PaymentTypeEnum::IMMEDIATE => 'checkout.type.immediate', // TODO Implement
                ),
                'attr' => array(
                    'class' => 'original',
                    'html' =>
                        // green message box for recurring payment
                        '<div class="tooltip-box type3 pie-el" ' .
                        'data-bind="visible: (\'recurring\' == payment.type() && !!payment.startDate())">' .
                        '<h4 data-bind="' .
                            'text: \'checkout.recurring.\' + payment.frequency() + \'.tooltip.title-%DUE_DAY%\', ' .
                            'i18n: {\'DUE_DAY\': payment.dueDate}' .
                        '"></h4>' .
                        '<p data-bind="' .
                            'text: \'checkout.recurring.\' + payment.frequency() + \'.\' + payment.ends() + ' .
                                '\'.tooltip.text-%AMOUNT%-%DUE_DAY%-%ENDS_ON%-%SETTLE_DAYS%\', ' .
                            'i18n: {' .
                                '\'AMOUNT\': getTotal, ' .
                                '\'DUE_DAY\': payment.dueDate, ' .
                                '\'SETTLE_DAYS\': settleDays, ' .
                                '\'ENDS_ON\': getLastPaymentDay' .
                            '}' .
                        '"></p></div>' .
                        // green message box for empty start_date
                        '<div class="tooltip-box type3 pie-el" data-bind="visible: !payment.startDate()">' .
                        '<h4 data-bind="text: Translator.trans(\'checkout.payment.choose_date.title\')"></h4>' .
                        '<p data-bind="text: Translator.trans(\'checkout.payment.choose_date.text\')"></p>' .
                        '</div>' .
                        // green message box for one_time payment
                        '<div class="tooltip-box type3 pie-el" ' .
                        'data-bind="visible: (\'one_time\' == payment.type() && !!payment.startDate())">'.
                        '<h4 data-bind="i18n: {\'START\': payment.startDate, \'SETTLE\': settle}">' .
                        'checkout.one_time.tooltip.title-%START%-%SETTLE%' .
                        '</h4>' .
                        '<p data-bind="i18n: {\'AMOUNT\': getTotal, \'START\': payment.startDate}">' .
                        'checkout.one_time.tooltip.text-%AMOUNT%-%START%' .
                        '</p></div>'
                    ,
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
        $builder->add(
            'dueDate',
            'choice',
            array(
                'label' => false,
                'choices' => $this->dueDays,
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => "options: payment.dueDates," .
                        "value: payment.dueDate, optionsCaption: ''",
                    'box_attr' => array(
                        'data-bind' => 'visible: \'monthly\' == payment.frequency()'
                    )
                ),
                'invalid_message' => 'checkout.error.dueDate.invalid',
                'invalid_message_parameters' => array(
                    '%OPEN_DATE%' => current($this->dueDays),
                    '%CLOSE_DATE%' => end($this->dueDays)
                )
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
                    'data-bind' => '
                        options: payment.startMonths,
                        value: payment.startMonth,
                        optionsCaption: "",
                        optionsText: "name",
                        optionsValue: "number"
                        ',
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
                    'data-bind' => '
                        options: payment.startYears,
                        value: payment.startYear,
                        optionsCaption: "",
                        optionsText: "name",
                        optionsValue: "number"
                        ',
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
                'mapped'          => false,
                'label'           => 'checkout.date',
                'input'           => 'string',
                'widget'          => 'single_text',
                'format'          => 'MM/dd/yyyy',
                'empty_data'      => '',
                'attr'            => array(
                    'class' => 'datepicker-field',
                    'row_attr'  => array(
                        'data-bind' => 'visible: \'one_time\' == payment.type()
                            || contract.groupSetting.pay_balance_only'
                    ),
                    'data-bind' => 'datepicker: payment.startDate, ' .
                        'datepickerOptions: { minDate: new Date(), dateFormat: \'m/d/yy\', beforeShowDay: isDueDay }',
                ),
                'invalid_message' => 'checkout.error.date.valid',
                'constraints'     => array(
                    new Date(
                        array(
                            'groups'  => array('one_time'),
                            'message' => 'checkout.error.date.valid',
                        )
                    ),
                    new StartDate(
                        array(
                            'groups'            => array(
                                'recurring',
                                'one_time'
                            ),
                            'oneTimeUntilValue' => $this->oneTimeUntilValue,
                        )
                    ),
                    new DayRange(
                        array(
                            'groups'            => array(
                                'recurring',
                                'one_time'
                            ),
                            'openDay'    => $this->openDay,
                            'closeDay'   => $this->closeDay
                        )
                    ),
                    new Callback(
                        array(
                            'groups'  => array('one_time'),
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
                    )
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
                    'data-bind' => 'value: payment.endMonth, enable: \'on\' == payment.ends()',
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
                    'data-bind' => 'value: payment.endYear, enable: \'on\' == payment.ends()',
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
        return static::NAME;
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
