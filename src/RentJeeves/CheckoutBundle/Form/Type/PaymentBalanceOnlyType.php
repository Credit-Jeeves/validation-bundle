<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use RentJeeves\CheckoutBundle\Constraint as CheckoutAssert;
use RentJeeves\CheckoutBundle\Constraint\StartDateValidator;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface as FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface as OptionsResolver;
use RentJeeves\CheckoutBundle\Form\AttributeGenerator\AttributeGeneratorInterface;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentBalanceOnlyType extends PaymentType
{
    const NAME = 'rentjeeves_checkoutbundle_paymentbalanceonlytype';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        /** @var AttributeGeneratorInterface $attributes */
        $attributes = $options['attributes'];

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
                'attr'            => $attributes->startDateAttrs(
                    StartDateValidator::isPastCutoffTime(new \DateTime(), $options['one_time_until_value'])
                ),
                'invalid_message' => 'checkout.error.date.valid',
                'constraints'     => [
                    new Assert\Date(
                        [
                            'groups'  => ['one_time'],
                            'message' => 'checkout.error.date.valid',
                        ]
                    ),
                    new CheckoutAssert\StartDate(
                        [
                            'groups'            => [
                                'recurring',
                                'one_time'
                            ],
                            'oneTimeUntilValue' => $options['one_time_until_value'],
                        ]
                    ),
                    new CheckoutAssert\DayRange(
                        [
                            'groups' =>[
                                'recurring',
                                'one_time'
                            ],
                            'openDay' => $options['open_day'],
                            'closeDay' => $options['close_day']
                        ]
                    ),
                    new Assert\Callback(
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
                'attr' => $attributes->submitAttrs()
            ]
        );

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

        $builder->remove('ends');

        $em = $options['em'];

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($em) {

                $contractId = $event->getForm()->get('contractId')->getData();
                $contract = $em->getRepository('RjDataBundle:Contract')
                    ->find($contractId);

                $paymentEntity = $event->getForm()->getData();

                $paymentEntity->setTotal($contract->getIntegratedBalance());
                $paymentEntity->setAmount($contract->getIntegratedBalance());

                $startDate = $event->getForm()->get('start_date')->getData();
                $startDate = DateTime::createFromFormat('Y-m-d', $startDate);
                if ($startDate) {
                    $paymentEntity->setDueDate($startDate->format('j'));
                    $paymentEntity->setStartMonth($startDate->format('n'));
                    $paymentEntity->setStartYear($startDate->format('Y'));
                }

                $event->setData($paymentEntity);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults([
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\Payment',
                'validation_groups'     => [PaymentTypeEnum::ONE_TIME],
        ]);

        $resolver->setRequired(['em']);

        $resolver->setAllowedTypes(['em' => 'Doctrine\Common\Persistence\ObjectManager']);
    }
}
