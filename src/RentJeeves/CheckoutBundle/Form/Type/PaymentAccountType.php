<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Model\User;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\BankAccountType;
use RentJeeves\DataBundle\Enum\PaymentAccountType as PaymentAccountTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\CardScheme;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ExecutionContextInterface;

class PaymentAccountType extends AbstractType
{
    const NAME = 'rentjeeves_checkoutbundle_paymentaccounttype';

    /**
     * @var Tenant
     */
    protected $user;

    /**
     * @var string
     */
    protected $formNameSuffix = '';

    /**
     * @param User $user
     * @param string $formNameSuffix
     */
    public function __construct(User $user, $formNameSuffix = '')
    {
        $this->user = $user;
        $this->formNameSuffix = $formNameSuffix ? '_' . $formNameSuffix : '';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'type',
            'choice',
            [
                'label' => 'checkout.payment_type',
                'expanded' => true,
                'choices' => [
                    PaymentAccountTypeEnum::BANK => 'checkout.bank',
                    PaymentAccountTypeEnum::CARD => 'checkout.card',
                    PaymentAccountTypeEnum::DEBIT_CARD => 'checkout.debit_card',
                ],
                'empty_value'  => false,
                'data'  => PaymentAccountTypeEnum::BANK,
                'attr' => [
                    'class' => 'payment_source_type',
                    'data-bind' => 'checked: currentPaymentAccount().type',
                    'html' => '<div
                            data-bind="visible: \'debit_card\' == currentPaymentAccount().type()"
                            class="tooltip-box type5 pie-el">' .
                        '<h4 data-bind="i18n: {}">checkout.type.tooltip.title</h4>' .
                        '</div>',
                ],
                'invalid_message' => 'checkout.error.type.invalid',
                'constraints' => [
                    new Callback([
                        'callback' => [$this, 'isValidPaymentAccountType'],
                        'groups' => ['bank', 'card', 'debit_card'],
                    ])
                ]
             ]
        );

        $builder->add(
            'name',
            'text',
            [
                'label' => 'checkout.account_nickname',
                'attr' => [
                    'data-bind' => 'value: currentPaymentAccount().name',
                ]
            ]
        );

        $builder->add(
            'PayorName',
            'text',
            [
                'mapped' => false,
                'label' => 'checkout.payor_name',
                'attr' => [
                    'placeholder' => 'checkout.payor_name.placeholder',
                    'data-bind' => 'value: currentPaymentAccount().PayorName',
                    'row_attr' => [
                        'data-bind' => 'visible: \'bank\' == currentPaymentAccount().type()'
                    ]
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['bank'],
                            'message' => 'checkout.error.payor_name.empty',
                        ]
                    ),
                    new Regex(
                        [
                            'message' => 'checkout.error.payor_name.invalid',
                            'pattern' => '/^(\w+\s){1,2}\w+$/'
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'RoutingNumber',
            'text',
            [
                'mapped' => false,
                'label' => 'checkout.routing_number',
                'attr' => [
                    'html' => '<div class="tooltip-box type2 pie-el">' .
                        '<h4 data-bind="i18n: {}">checkout.routing_number.tooltip.title</h4>' .
                        '<p class="banking-numbers clearfix"></p>' .
                        '</div>',
                    'tooltip_text_class' => 'banking-numbers clearfix',
                    'data-bind' => 'value: currentPaymentAccount().RoutingNumber',
                    'row_attr' => [
                        'data-bind' => 'visible: \'bank\' == currentPaymentAccount().type()'
                    ]
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['bank'],
                            'message' => 'checkout.error.routing_number.empty',
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'AccountNumber',
            'repeated',
            [
                'first_name' => 'AccountNumber',
                'second_name' => 'AccountNumberAgain',
                'first_options'  => [
                    'label' => 'checkout.account_number',
                    'attr' => [
                        'data-bind' => 'value: currentPaymentAccount().AccountNumber',
                    ],
                ],
                'second_options' => [
                    'label' => 'checkout.account_number_again',
                    'label_attr' => [
                        'class' => 'clear',
                    ],
                    'attr' => [
                        'data-bind' => 'value: currentPaymentAccount().AccountNumberAgain',
                    ],
                ],
                'invalid_message' => 'checkout.error.account_number.match',
                'type' => 'text',
                'mapped' => false,
                'label' => 'checkout.account_number',
                'attr' => [
                    'row_attr' => [
                        'data-bind' => 'visible: \'bank\' == currentPaymentAccount().type()'
                    ],
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['bank'],
                            'message' => 'checkout.error.account_number.empty',
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'ACHDepositType',
            'choice',
            [
                'property_path' => 'bankAccountType',
                'label' => 'checkout.account_type',
                'expanded' => true,
                'choices' => [
                    BankAccountType::CHECKING => 'checkout.account_type.checking',
                    BankAccountType::SAVINGS => 'checkout.account_type.savings',
                    BankAccountType::BUSINESS_CHECKING => 'checkout.account_type.business_checking'
                ],
                'empty_value'  => false,
                'invalid_message' => 'checkout.error.account_type.invalid',
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['bank'],
                            'message' => 'checkout.error.account_type.empty',
                        ]
                    ),
                ],
                'attr' => [
                    'data-bind' => 'checked: currentPaymentAccount().ACHDepositType',
                    'row_attr' => [
                        'data-bind' => 'visible: \'bank\' == currentPaymentAccount().type()'
                    ]
                ]
            ]
        );

        $builder->add(
            'CardAccountName',
            'text',
            [
                'mapped' => false,
                'label' => 'checkout.card.account_name',
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['card', 'debit_card'],
                            'message' => 'checkout.error.card_account_name.empty',
                        ]
                    ),
                ],
                'attr' => [
                    'data-bind' => 'value: currentPaymentAccount().CardAccountName',
                    'row_attr' => [
                        'data-bind' => 'visible: ["card", "debit_card"].indexOf(currentPaymentAccount().type()) !== -1'
                    ]
                ]
            ]
        );

        $builder->add(
            'CardNumber',
            'text',
            [
                'mapped' => false,
                'label' => 'checkout.card_number',
                'attr' => [
                    'html' => '<ul class="cc-box clearfix">' .
                        '<li><span class="cc visa">visa</span></li>' .
                        '<li><span class="cc mc">mastercard</span></li>' .
//                        '<li><span class="cc ae">american express</span></li>' .
                        '<li data-bind="visible: \'card\' == currentPaymentAccount().type()">' .
                            '<span class="cc discover">discover</span>' .
                        '</li>' .
//                        '<li><span class="cc dc">diners club</span></li>',
                        '</ul>',
                    'data-bind' => 'value: currentPaymentAccount().CardNumber',
                    'row_attr' => [
                        'data-bind' => 'visible: ["card", "debit_card"].indexOf(currentPaymentAccount().type()) !== -1'
                    ]
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['card', 'debit_card'],
                            'message' => 'checkout.error.card_number.empty',
                        ]
                    ),
                    new CardScheme(
                        [
                            'groups' => ['card'],
                            'schemes' => [
                                'VISA',
                                'MASTERCARD',
                                'DISCOVER'
                            ]
                        ]
                    ),
                    new CardScheme(
                        [
                            'groups' => ['debit_card'],
                            'schemes' => [
                                'VISA',
                                'MASTERCARD',
                            ]
                        ]
                    )
                ]
            ]
        );

        $builder->add(
            'VerificationCode',
            'password',
            [
                'mapped' => false,
                'label' => 'checkout.csc',
                'attr' => [
                    'class' => 'phone-width',
                    'help' => 'checkout.csc.help',
                    'data-bind' => 'value: currentPaymentAccount().VerificationCode',
                    'row_attr' => [
                        'data-bind' => 'visible: ["card", "debit_card"].indexOf(currentPaymentAccount().type()) !== -1'
                    ]
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['card', 'debit_card'],
                            'message' => 'checkout.error.csc.empty',
                        ]
                    ),
                    new Regex(
                        [
                            'pattern' => '/^[0-9]{3}$/',
                            'groups' => ['card', 'debit_card'],
                            'message' => 'checkout.error.csc.type',
                        ]
                    )
                ]
            ]
        );

        $builder->add(
            'ExpirationMonth',
            'choice',
            [
                'mapped' => false,
                'label' => 'checkout.expiration',
                'choices' => array_combine(range(1, 12, 1), range(1, 12, 1)),
                'empty_value'  => 'common.month',
                'attr' => [
                    'class' => 'original',
                    'data-bind' => 'value: currentPaymentAccount().ExpirationMonth',
                    'row_attr' => [
                        'data-bind' => 'visible: ["card", "debit_card"].indexOf(currentPaymentAccount().type()) !== -1'
                    ]
                ],
                'invalid_message' => 'checkout.error.ExpirationMonth.invalid',
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['card', 'debit_card'],
                            'message' => 'checkout.error.ExpirationMonth.empty',
                        ]
                    ),
                ],
            ]
        );

        $years = range(date('Y'), date('Y')+12, 1);

        $builder->add(
            'ExpirationYear',
            'choice',
            [
                'mapped' => false,
                'label' => false,
                'choices' => array_combine($years, $years),
                'empty_value'  => 'common.year',
                'attr' => [
                    'class' => 'original',
                    'data-bind' => 'value: currentPaymentAccount().ExpirationYear',
                    'row_attr' => [
                        'data-bind' => 'visible: ["card", "debit_card"].indexOf(currentPaymentAccount().type()) !== -1'
                    ]
                ],
                'invalid_message' => 'checkout.error.ExpirationYear.invalid',
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['card', 'debit_card'],
                            'message' => 'checkout.error.ExpirationYear.empty',
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'address_choice',
            'entity',
            [
                'class' => 'CreditJeeves\DataBundle\Entity\MailingAddress',
                'mapped' => true,
                'property_path' => 'address',
                'label' => 'checkout.billing_address',
                'expanded' => true,
                'choices' => clone $this->user->getAddresses(),
                'attr' => [
                    'data-bind' => 'checked: billingaddress.addressChoice',
                    'row_attr' => [
                        'data-bind' => 'visible: ' .
                            '(["card", "debit_card"].indexOf(currentPaymentAccount().type()) !== -1' .
                            ' && addresses().length > 0)'
                    ],
                    'html' =>
                        '<!-- ko foreach: newAddresses -->' .
                            '<label class="checkbox radio">' .
                                '<input type="radio" name="' . $this->getName() . '[address_choice]" ' .
                                    'required="required" ' .
                                    'data-bind="' .
                                        'checked: $parent.billingaddress.addressChoice, ' .
                                        'attr: {\'id\': \'' . $this->getName() . '_address_choice_\' + $data.id() }, ' .
                                        'value: $data.id()' .
                                    '" />' .
                                '<i></i>' .
                                '<span data-bind="text: $data.toString()"></span>' .
                            '</label>' .
                        '<!-- /ko -->'
                ],
                'invalid_message' => 'checkout.error.address_choice.invalid',
                'constraints' => [
                    new NotBlank(
                        [
                            'groups' => ['address_choice'],
                            'message' => 'checkout.error.address_choice.empty',
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'is_new_address',
            'hidden',
            [
                'mapped' => false,
                'attr' => [
                    'data-bind' => 'value: billingaddress.isAddNewAddress'
                ]
            ]
        );

        $builder->add(
            'is_new_address_link',
            'text',
            [
                'mapped' => false,
                'label' => false,
                'attr' => [
                    'data-bind' => 'visible: false',
                    'row_attr' => [
                        'data-bind' => 'visible: ["card", "debit_card"].indexOf(currentPaymentAccount().type()) !== -1'
                    ],
                    'html' => '<div class="fields-box" data-bind="visible: !billingaddress.isAddNewAddress()">' .
                        '<a href="#" data-bind="i18n: {}, click: billingaddress.addAddress">' .
                        'common.add_new' .
                        '</a>' .
                        '</div>',
                    'force_row' => true,
                ]
            ]
        );

        $builder->add(
            'address',
            new UserAddressType('billing'),
            [
                'mapped' => false,
                'label' => 'checkout.billing_address.new',
                'by_reference' => true,
                'attr' => [
                    'no_box' => true,
                    'force_row' => true,
                    'row_attr' => [
                        'data-bind' => 'visible: ' .
                            '(["card", "debit_card"].indexOf(currentPaymentAccount().type()) !== -1' .
                            ' && billingaddress.isAddNewAddress())',
                        'class' => 'form-row-custom clearfix type-text'
                    ]
                ]
            ]
        );

        $builder->add('submit', 'submit', ['attr' => ['force_row' => true, 'class' => 'hide_submit']]);

        $builder->add('id', 'hidden', ['attr' => ['data-bind' => 'value: currentPaymentAccount().id']]);

        $builder->add(
            'contract',
            'entity_hidden',
            [
                'mapped' => false,
                'class' => 'RentJeeves\DataBundle\Entity\Contract',
                    'attr' => [
                    'data-bind' => 'value: currentPaymentAccount().contractId',
                ]
            ]
        );

        $builder->add(
            'group',
            'entity_hidden',
            [
                'mapped' => false,
                'class' => 'CreditJeeves\DataBundle\Entity\Group',
                'attr' => [
                    'data-bind' => 'value: $root.paymentGroupId ? $root.paymentGroupId : null',
                ]
            ]
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'cascade_validation' => true,
                'data_class' => 'RentJeeves\DataBundle\Entity\PaymentAccount',
                'validation_groups' => function (FormInterface $form) {
                    $data = $form->getData();
                    $type = $data->getType();
                    $groups = [];
                    if (PaymentAccountTypeEnum::CARD == $type || PaymentAccountTypeEnum::DEBIT_CARD == $type) {
                        if ('false' == $form->get('is_new_address')->getData()) {
                            $groups[] = 'address_choice';
                        }
                        if ('true' == $form->get('is_new_address')->getData()) {
                            $groups[] = 'user_address_new';
                        }
                    }
                    $groups[] = $type;

                    return $groups;
                }
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME . $this->formNameSuffix;
    }

    /**
     * @param $data
     * @param ExecutionContextInterface $context
     */
    public function isValidPaymentAccountType($data, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        /** @var Contract $contract */
        $contract = $form->get('contract')->getData();
        /** @var Group $group */
        $group = $form->get('group')->getData();
        if (!$contract && !$group) {
            return;
        }
        $groupSettings = $contract ? $contract->getGroupSettings() : $group->getGroupSettings();
        switch ($data) {
            case PaymentAccountTypeEnum::BANK:
                $valid = $groupSettings->isAllowedACH();
                break;
            case PaymentAccountTypeEnum::CARD:
                $valid = $groupSettings->isAllowedCreditCard();
                break;
            case PaymentAccountTypeEnum::DEBIT_CARD:
                $valid = $groupSettings->isAllowedCreditCard() && $groupSettings->isAllowedDebitFee();
                break;
            default:
                $context->addViolation('checkout.error.type.invalid');
                return;
        }
        if (!$valid) {
            $context->addViolation('checkout.error.type.disallow', ['%value%' => $data]);
        }
    }
}
