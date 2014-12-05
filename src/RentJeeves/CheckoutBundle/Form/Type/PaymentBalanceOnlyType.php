<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\CoreBundle\DateTime;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;

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
        $closeDay
    ) {
        parent::__construct(
            $oneTimeUntilValue,
            $paidFor,
            $dueDays,
            $openDay,
            $closeDay
        );
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('type');
        $builder->add(
            'type',
            'choice',
            array(
                'label' => 'checkout.type',
                'position' => array('before' => 'start_date'),
                'empty_data' => PaymentTypeEnum::ONE_TIME,
                'choices' => array(
                    PaymentTypeEnum::ONE_TIME => 'checkout.type.one_time',
                ),
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: payment.type',
                    'row_attr' => array(
                        'data-bind' => ''
                    )
                ),
                'invalid_message' => 'checkout.error.type.invalid',
            )
        );


        $builder->remove('amount');
        $builder->remove('paidFor');
        $builder->remove('amountOther');
        $builder->remove('total');
        $builder->remove('frequency');
        $builder->remove('dueDate');
        $builder->remove('startMonth');
        $builder->remove('startYear');
        $builder->remove('startMonth');
        $builder->remove('ends');
        $builder->remove('endMonth');
        $builder->remove('endYear');

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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\Payment',
                'validation_groups'     => array(PaymentTypeEnum::ONE_TIME),
            )
        );
    }
}
