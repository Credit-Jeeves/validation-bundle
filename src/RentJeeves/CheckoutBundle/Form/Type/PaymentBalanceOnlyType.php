<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use RentJeeves\CheckoutBundle\Constraint\DayRange;
use RentJeeves\CheckoutBundle\Constraint\StartDate;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorInterface;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;

class PaymentBalanceOnlyType extends PaymentType
{
    const NAME = 'rentjeeves_checkoutbundle_paymentbalanceonlytype';

    protected $em;

    public function __construct(
        $oneTimeUntilValue,
        array $paidFor,
        $dueDays,
        $em,
        $openDay,
        $closeDay,
        AttributeGeneratorInterface $attributes
    ) {
        parent::__construct(
            $oneTimeUntilValue,
            $paidFor,
            $dueDays,
            $openDay,
            $closeDay,
            $attributes
        );

        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->attributes->isMobile()) {
            $builder->add(
                'paymentAccount',
                'choice',
                [
                    'choices' => [],
                    'attr' => $this->attributes->paymentAccountAttrs(),
                    'required' => false,
                ]
            );
        }

        $builder->add(
            'type',
            'choice',
            [
                'label' => 'checkout.type',
                'empty_data' => PaymentTypeEnum::ONE_TIME,
                'choices' => [
                    PaymentTypeEnum::ONE_TIME => 'checkout.type.one_time',
                ],
                'attr' => [
                    'class' => 'original',
                    'data-bind' => 'value: payment.type',
                    'row_attr' => [
                        'data-bind' => ''
                    ]
                ],
                'invalid_message' => 'checkout.error.type.invalid',
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
                'attr'            => $this->attributes->startDateAttrs(),
                'invalid_message' => 'checkout.error.date.valid',
                'constraints'     => [
                    new Date(
                        [
                            'groups'  => ['one_time'],
                            'message' => 'checkout.error.date.valid',
                        ]
                    ),
                    new StartDate(
                        [
                            'groups'            => [
                                'recurring',
                                'one_time'
                            ],
                            'oneTimeUntilValue' => $this->oneTimeUntilValue,
                        ]
                    ),
                    new DayRange(
                        [
                            'groups' =>[
                                'recurring',
                                'one_time'
                            ],
                            'openDay' => $this->openDay,
                            'closeDay' => $this->closeDay
                        ]
                    ),
                    new Callback(
                        [
                            'groups'  => ['one_time'],
                            'methods' => [[$this, 'isLaterOrEqualNow']]
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'next',
            'submit',
            [
                'attr' => $this->attributes->submitAttrs()
            ]
        );

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

        $builder->remove('ends');

        $self = $this;
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($self) {

                $contractId = $event->getForm()->get('contractId')->getData();
                $contract = $self->em->getRepository('RjDataBundle:Contract')
                    ->find($contractId);

                $paymentEntity = $event->getForm()->getData();

                $paymentEntity->setTotal($contract->getIntegratedBalance());
                $paymentEntity->setAmount($contract->getIntegratedBalance());

                $startDate = $event->getForm()->get('start_date')->getData();
                $startDate = DateTime::createFromFormat('Y-m-d', $startDate);
                $paymentEntity->setDueDate($startDate->format('j'));
                $paymentEntity->setStartMonth($startDate->format('n'));
                $paymentEntity->setStartYear($startDate->format('Y'));

                $event->setData($paymentEntity);
            }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\Payment',
                'validation_groups'     => [PaymentTypeEnum::ONE_TIME],
            ]
        );
    }
}
