<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use RentJeeves\CheckoutBundle\Constraint\DayRange;
use RentJeeves\CheckoutBundle\Constraint\StartDate;
use RentJeeves\CheckoutBundle\Constraint\StartDateValidator;
use RentJeeves\CheckoutBundle\Form\DataTransformer\DateTimeToStringTransformer;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorInterface;
use RentJeeves\CoreBundle\Form\Type\ViewHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Callback;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @var AttributeGeneratorInterface
     */
    protected $attributes;

    /**
     * @param string $oneTimeUntilValue
     */
    public function __construct(
        $oneTimeUntilValue,
        array $paidFor,
        $dueDays,
        $openDay,
        $closeDay,
        AttributeGeneratorInterface $attributes
    ) {
        $this->oneTimeUntilValue = $oneTimeUntilValue;
        $this->paidFor = $paidFor;
        $this->dueDays = $dueDays;
        $this->openDay = $openDay;
        $this->closeDay = $closeDay;
        $this->attributes = $attributes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'amount',
            null,
            array(
                'required' => false,
                'label' => 'checkout.amount',
                'attr' => $this->attributes->amountAttrs(),
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
                    'attr' => $this->attributes->paidForAttrs()
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
                'attr' => $this->attributes->amountOtherAttrs(),
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

        if ($this->attributes->isMobile()) {
            $builder->add(
                'paymentAccount',
                'choice',
                array(
                    'choices' => array(),
                    'attr' => $this->attributes->paymentAccountAttrs(),
                    'required' => false,
                )
            );
        }

        $builder->add(
            'total',
            new ViewHiddenType(),
            array(
                'label' => 'checkout.total',
                'required' => true,
                'attr' => $this->attributes->totalAttrs()
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
                'attr' => $this->attributes->typeAttrs(),
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
                'attr' => $this->attributes->frequencyAttrs(),
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
                'attr' => $this->attributes->dueDateAttrs(),
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
                'attr' => $this->attributes->startMonthAttrs(),
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
                'attr' => $this->attributes->startYearAttrs(),
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
                'attr'            => $this->attributes->startDateAttrs(
                    StartDateValidator::isPastCutoffTime(new \DateTime(), $this->oneTimeUntilValue)
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
                'attr' => $this->attributes->endsAttrs(),
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
                'attr' => $this->attributes->endMonthAttrs(),
                'invalid_message' => 'checkout.error.endMonth.invalid',
            )
        );
        $builder->add(
            'endYear',
            'choice',
            array(
                'label' => false,
                'choices' => array_combine($years, $years),
                'attr' => $this->attributes->endYearAttrs(),
                'invalid_message' => 'checkout.error.endYear.invalid',
            )
        );
        $builder->add('next', 'submit', array('attr' => $this->attributes->submitAttrs()));
        $builder->add(
            'paymentAccountId',
            'hidden',
            array(
                'mapped' => false,
                'attr' => $this->attributes->paymentAccountIdAttrs()
            )
        );
        $builder->add(
            'contractId',
            'hidden',
            array(
                'mapped' => false,
                'attr' => $this->attributes->contractIdAttrs()
            )
        );
        $builder->add(
            'id',
            'hidden',
            array(
                'attr' => $this->attributes->idAttrs()
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
        $now = new \DateTime();
        $now->setTime(0, 0);

        $payDate = new \DateTime($data);
        if ($payDate < $now) {
            $validatorContext->addViolationAt('start_date', 'checkout.error.date.is_in_past', array(), null);
        }
    }
}
