<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use RentJeeves\CheckoutBundle\Form\Type\UserAddressType;
use CreditJeeves\CoreBundle\Form\Widget\MonthYearType;
use CreditJeeves\DataBundle\Model\User;
use Doctrine\ORM\EntityRepository;
use Payum\Heartland\Soap\Base\ACHAccountType;
use Payum\Heartland\Soap\Base\ACHDepositType;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PaymentAccountType extends AbstractType
{
    /**
     * @var Tenant
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'type',
            'choice',
            array(
                'label' => 'checkout.payment_type',
                'expanded' => true,
                'choices' => array(
                    PaymentAccountTypeEnum::BANK => 'checkout.bank',
                    PaymentAccountTypeEnum::CARD => 'checkout.card'
                ),
                'empty_value'  => false,
                'data'  => PaymentAccountTypeEnum::BANK,
                'attr' => array(
                    'data-bind' => 'checked: paymentSource.type'
                )
            )
        );
        $builder->add(
            'name',
            'text',
            array(
                'label' => 'checkout.account_nickname',
                'attr' => array(
                    'data-bind' => 'value: paymentSource.name',
                    'row_attr' => array(
                        'data-bind' => 'visible: paymentSource.save'
                    )
                )
            )
        );
        $builder->add(
            'PayorName',
            'text',
            array(
                'mapped' => false,
                'label' => 'checkout.payor_name',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('bank'),
                            'message' => 'checkout.error.payor_name.empty',
                        )
                    ),
                ),
                'attr' => array(
                    'data-bind' => 'value: paymentSource.PayorName',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'bank\' == paymentSource.type()'
                    )
                )
            )
        );
        $builder->add(
            'RoutingNumber',
            'text',
            array(
                'mapped' => false,
                'label' => 'checkout.routing_number',
                'attr' => array(
                    'html' => '<div class="tooltip-box type2 pie-el">' .
                            '<h4 data-bind="i18n: {}">checkout.routing_number.tooltip.title</h4>' .
                            '<p class="banking-numbers clearfix"></p>' .
                        '</div>',
                    'tooltip_text_class' => 'banking-numbers clearfix',
                    'data-bind' => 'value: paymentSource.RoutingNumber',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'bank\' == paymentSource.type()'
                    )
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('bank'),
                            'message' => 'checkout.error.routing_number.empty',
                        )
                    ),
                ),
            )
        );
        $builder->add(
            'AccountNumber',
            'text',
            array(
                'mapped' => false,
                'label' => 'checkout.account_number',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('bank'),
                            'message' => 'checkout.error.account_number.empty',
                        )
                    ),
                ),
                'attr' => array(
                    'data-bind' => 'value: paymentSource.AccountNumber',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'bank\' == paymentSource.type()'
                    )
                )
            )
        );
        $builder->add(
            'ACHDepositType',
            'choice',
            array(
                'mapped' => false,
                'label' => 'checkout.account_type',
                'expanded' => true,
                'choices' => array(
                    ACHDepositType::CHECKING => 'checkout.account_type.checking',
                    ACHDepositType::SAVINGS => 'checkout.account_type.savings',
                    ACHDepositType::UNASSIGNED => 'checkout.account_type.business_checking'
                ),
                'empty_value'  => false,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('bank'),
                            'message' => 'checkout.error.account_type.empty',
                        )
                    ),
                ),
                'attr' => array(
                    'data-bind' => 'checked: paymentSource.ACHDepositType',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'bank\' == paymentSource.type()'
                    )
                )
            )
        );
        $builder->add(
            'CardNumber',
            'text',
            array(
                'mapped' => false,
                'label' => 'checkout.card_number',
                'attr' => array(
                    'html' => '<ul class="cc-box clearfix">' .
                        '<li><span class="cc visa">visa</span></li>' .
                        '<li><span class="cc mc">mastercard</span></li>' .
                        '<li><span class="cc ae">american express</span></li>' .
                        '<li><span class="cc discover">discover</span></li>' .
                        '<li><span class="cc dc">diners club</span></li></ul>',
                    'data-bind' => 'value: paymentSource.CardNumber',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type()'
                    )
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('card'),
                            'message' => 'checkout.error.card_number.empty',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'VerificationCode',
            'password',
            array(
                'mapped' => false,
                'label' => 'checkout.csc',
                'attr' => array(
                    'class' => 'phone-width',
                    'help' => 'checkout.csc.help',
                    'data-bind' => 'value: paymentSource.VerificationCode',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type()'
                    )
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('card'),
                            'message' => 'checkout.error.csc.empty',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'ExpirationMonth',
            'choice',
            array(
                'mapped' => false,
                'label' => 'checkout.expiration',
                'choices' => array_combine(range(1, 12, 1), range(1, 12, 1)),
                'empty_value'  => 'common.month',
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: paymentSource.ExpirationMonth',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type()'
                    )
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('card'),
                            'message' => 'checkout.error.expiration.month.empty',
                        )
                    ),
                ),
            )
        );
        $years = range(date('Y'), date('Y')+12, 1);
        $builder->add(
            'ExpirationYear',
            'choice',
            array(
                'mapped' => false,
                'label' => false,
                'choices' => array_combine($years, $years),
                'empty_value'  => 'common.year',
                'attr' => array(
                    'class' => 'original',
                    'data-bind' => 'value: paymentSource.ExpirationYear',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type()'
                    )
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('card'),
                            'message' => 'checkout.error.expiration.year.empty',
                        )
                    ),
                )
            )
        );

        $builder->add(
            'address_choice',
            'entity',
            array(
                'class' => 'CreditJeeves\DataBundle\Entity\Address',
                'mapped' => false,
                'label' => 'common.address',
                'expanded' => true,
                'choices' => $this->user->getAddresses(),
                'attr' => array(
                    'data-bind' => 'checked: address.addressChoice',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type()'
                    ),
                    'html' => '<div class="fields-box" data-bind="visible: !address.isAddNewAddress()">' .
                        '<a href="#" data-bind="i18n: {}, click: address.addAddress">common.add_new</a></div>'
                )
            )
        );
        $builder->add(
            'address',
            new UserAddressType(),
            array(
                'mapped' => true,
                'label' => false,
                'by_reference' => true,
                'attr' => array(
                    'no_box' => true,
                    'force_row' => true,
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type() && address.isAddNewAddress'
                    )
                )
            )
        );


        $builder->add(
            'save',
            'checkbox',
            array(
                'mapped' => false,
                'label' => 'checkout.payment.save',
                'attr' => array(
                    'help' => 'checkout.payment.save.help',
                    'data-bind' => 'checked: paymentSource.save',
                    'row_attr' => array(
                        'data-bind' => 'visible: !paymentSource.isForceSave()'
                    )
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'RentJeeves\DataBundle\Entity\PaymentAccount',
                'validation_groups' => function (FormInterface $form) {
                    $data = $form->getData();
                    $type = $data->getType();
                    $groups = array();
                    if (PaymentAccountTypeEnum::CARD == $type) {
                        $groups[] = 'user_address_new';
                    }
                    if ($form->get('save')->getData()) {
                        $groups[] = 'save';
                    }

                    $groups[] = $type;

                    return $groups;
                }
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_checkoutbundle_paymentaccounttype';
    }
}
