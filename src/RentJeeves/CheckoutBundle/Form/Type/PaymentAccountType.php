<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\DataBundle\Model\User;
use RentJeeves\CheckoutBundle\Form\Enum\ACHDepositTypeEnum;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PaymentAccountType extends AbstractType
{
    const NAME = 'rentjeeves_checkoutbundle_paymentaccounttype';

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
                ),
                'invalid_message' => 'checkout.error.payment_type.invalid',
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
                    new Regex(
                        array(
                            'message' => 'checkout.error.payor_name.invalid',
                            'pattern' => '/^(\w+\s){1,2}\w+$/'
                        )
                    )
                ),
                'attr' => array(
                    'placeholder' => 'checkout.payor_name.placeholder',
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
            'repeated',
            array(
                'first_name' => 'AccountNumber',
                'second_name' => 'AccountNumberAgain',
                'first_options'  => array(
                    'label' => 'checkout.account_number'
                ),
                'second_options' => array(
                    'label' => 'checkout.account_number_again',
                    'label_attr' => array(
                        'class' => 'clear',
                    ),
                ),
                'invalid_message' => 'checkout.error.account_number.match',
                'type' => 'text',
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
                    ACHDepositTypeEnum::CHECKING => 'checkout.account_type.checking',
                    ACHDepositTypeEnum::SAVINGS => 'checkout.account_type.savings',
                    ACHDepositTypeEnum::BUSINESS_CHECKING => 'checkout.account_type.business_checking'
                ),
                'empty_value'  => false,
                'invalid_message' => 'checkout.error.account_type.invalid',
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
            'CardAccountName',
            'text',
            array(
                'mapped' => false,
                'label' => 'checkout.card.account_name',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('card'),
                            'message' => 'checkout.error.card_account_name.empty',
                        )
                    ),
                ),
                'attr' => array(
                    'data-bind' => 'value: paymentSource.CardAccountName',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type()'
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
//                        '<li><span class="cc ae">american express</span></li>' .
                        '<li><span class="cc discover">discover</span></li>' .
//                        '<li><span class="cc dc">diners club</span></li>',
                        '</ul>',
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
                'invalid_message' => 'checkout.error.ExpirationMonth.invalid',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('card'),
                            'message' => 'checkout.error.ExpirationMonth.empty',
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
                'invalid_message' => 'checkout.error.ExpirationYear.invalid',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('card'),
                            'message' => 'checkout.error.ExpirationYear.empty',
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
                'label' => 'checkout.billing_address',
                'expanded' => true,
                'choices' => clone $this->user->getAddresses(),
                'attr' => array(
                    'data-bind' => 'checked: paymentSource.address.addressChoice',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type() && window.addressesViewModels.length'
                    ),
                    'html' =>
                        '<!-- ko foreach: newUserAddress -->' .
                            '<label class="checkbox radio">' .
                                '<input type="radio" name="' . $this->getName() . '[address_choice]"' .
                                    'required="required"' .
                                    'data-bind="' .
                                        'checked: $parent.paymentSource.address.addressChoice, ' .
                                        'attr: {\'id\': \'' . $this->getName() . '_address_choice_\' + $data.id() }, ' .
                                        'value: $data.id()' .
                                    '" />' .
                                '<i></i>' .
                                '<span data-bind="text: $data.toString()"></span>' .
                            '</label>' .
                        '<!-- /ko -->'
                ),
                'invalid_message' => 'checkout.error.address_choice.invalid',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('address_choice'),
                            'message' => 'checkout.error.address_choice.empty',
                        )
                    ),
                )
            )
        );
        $builder->add(
            'is_new_address',
            'hidden',
            array(
                'mapped' => false,
                'attr' => array(
                    'data-bind' => 'value: paymentSource.address.isAddNewAddress'
                )
            )
        );

        $builder->add(
            'is_new_address_link',
            'text',
            array(
                'mapped' => false,
                'label' => false,
                'attr' => array(
                    'data-bind' => 'visible: false',
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type()'
                    ),
                    'html' => '<div class="fields-box" data-bind="visible: !paymentSource.address.isAddNewAddress()">' .
                        '<a href="#" data-bind="i18n: {}, click: paymentSource.address.addAddress">' .
                        'common.add_new' .
                        '</a>' .
                        '</div>',
                    'force_row' => true,
                )
            )
        );

        $builder->add(
            'address',
            new UserAddressType('paymentSource.'),
            array(
                'mapped' => true,
                'label' => 'checkout.billing_address.new',
                'by_reference' => true,
                'attr' => array(
                    'no_box' => true,
                    'force_row' => true,
                    'row_attr' => array(
                        'data-bind' => 'visible: \'card\' == paymentSource.type() ' .
                            '&& paymentSource.address.isAddNewAddress()',
                        'class' => 'form-row-custom clearfix type-text'
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

        $builder->add('submit', 'submit', array('attr' => array('force_row' => true, 'class' => 'hide_submit')));
        $builder->add('id', 'hidden', array('attr' => array('data-bind' => 'value: paymentSource.id')));
        $builder->add(
            'groupId',
            'hidden',
            array(
                'mapped' => false,
                'attr' => array(
                    'data-bind' => 'value: paymentSource.groupId',
                )
            )
        );
        $builder->add(
            'contractId',
            'hidden',
            array(
                'mapped' => false,
                'attr' => array(
                    'data-bind' => 'value: paymentSource.contractId',
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
                        if ('false' == $form->get('is_new_address')->getData()) {
                            $groups[] = 'address_choice';
                        }
                        if ('true' == $form->get('is_new_address')->getData()) {
                            $groups[] = 'user_address_new';
                        }
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
        return static::NAME;
    }
}
