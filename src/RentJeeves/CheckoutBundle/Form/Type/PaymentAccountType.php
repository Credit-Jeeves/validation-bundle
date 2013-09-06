<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\CoreBundle\Form\Widget\MonthYearType;
use Payum\Heartland\Soap\Base\ACHAccountType;
use Payum\Heartland\Soap\Base\ACHDepositType;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PaymentAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'payment_type',
            'choice',
            array(
                'label' => 'checkout.payment_type',
                'expanded' => true,
                'choices' => array(
                    PaymentAccountTypeEnum::BANK => 'checkout.bank',
                    PaymentAccountTypeEnum::CARD => 'checkout.card'
                ),
                'empty_value'  => false,
            )
        );
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'checkout.account_nickname',
            )
        );
        $builder->add(
            'PayorName',
            'text',
            array(
                'label' => 'checkout.payor_name',
            )
        );
        $builder->add(
            'RoutingNumber',
            'text',
            array(
                'label' => 'checkout.routing_number',
                'attr' => array(
                    'tooltip_title' => 'checkout.routing_number.tooltip.title',
                    'tooltip_class' => 'tooltip-box type2 pie-el',
                    'tooltip_text' => '',
                    'tooltip_text_class' => 'banking-numbers clearfix',
                ),
            )
        );
        $builder->add(
            'AccountNumber',
            'text',
            array(
                'label' => 'checkout.account_number',
            )
        );
        $builder->add(
            'ACHDepositType',
            'choice',
            array(
                'label' => 'checkout.account_type',
                'expanded' => true,
                'choices' => array(
                    ACHDepositType::CHECKING => 'checkout.account_type.checking',
                    ACHDepositType::SAVINGS => 'checkout.account_type.savings',
                    ACHDepositType::UNASSIGNED => 'checkout.account_type.business_checking'
                ),
                'empty_value'  => false,
            )
        );
        $builder->add(
            'CardNumber',
            'text',
            array(
                'label' => 'checkout.card_number',
                'attr' => array(
                    'html' => '<ul class="cc-box clearfix">' .
                        '<li><span class="cc visa">visa</span></li>' .
                        '<li><span class="cc mc">mastercard</span></li>' .
                        '<li><span class="cc ae">american express</span></li>' .
                        '<li><span class="cc discover">discover</span></li>' .
                        '<li><span class="cc dc">diners club</span></li></ul>'
                )
            )
        );
        $builder->add(
            'VerificationCode',
            'text',
            array(
                'label' => 'checkout.csc',
                'attr' => array(
                    'class' => 'phone-width',
                    'help' => 'checkout.csc.help'
                )
            )
        );
        $builder->add(
            'ExpirationMonth',
            'choice',
            array(
                'label' => 'checkout.expiration',
                'choices' => range(1, 12, 1),
                'empty_value'  => 'common.month',
                'attr' => array(
                    'class' => 'original',
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('buy_report_new'),
                            'message' => 'checkout.error.expiration.empty',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'ExpirationYear',
            'choice',
            array(
                'label' => false,
                'choices' => range(date('Y'), date('Y')+12, 1),
                'empty_value'  => 'common.year',
                'attr' => array(
                    'class' => 'original',
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('buy_report_new'),
                            'message' => 'checkout.error.expiration.empty',
                        )
                    ),
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
        return 'rentjeeves_checkoutbundle_paymentaccounttype';
    }
}
