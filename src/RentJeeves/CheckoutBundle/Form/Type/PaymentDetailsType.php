<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PaymentDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('amount', 'text', array('attr' => array('class' => 'half-of-right')));
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
                    'class' => 'datepicker-field',
                    'tooltip_title' => 'checkout.start_date.tooltip.title',
                    'tooltip_text' => 'checkout.start_date.tooltip.text',
                ),

                'error_bubbling' => true,
            )
        );
        $builder->add('recurring', 'checkbox', array('label' => 'checkout.set_up_recurring_payment'));
        $builder->add(
            'type',
            'choice',
            array(
                'label' => 'checkout.type',
                'empty_data' => 'checkout.select',
                'choices' => array('monthly' => 'checkout.monthly'),
                'attr' => array(
                    'class' => 'original',
                    'tooltip_title' => 'checkout.type.tooltip.title',
                    'tooltip_text' => 'checkout.type.tooltip.text',
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
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
//                'data_class' => 'RentJeeves\DataBundle\Entity\Contract',
                'validation_groups' => array(
//                    'tenant_invite',
                ),
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_checkoutbundle_paymentdetailstype';
    }
}
