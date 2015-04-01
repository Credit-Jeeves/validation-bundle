<?php

namespace RentJeeves\LandlordBundle\Form;

use RentJeeves\CheckoutBundle\Form\Enum\ACHDepositType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class BillingAccountType extends AbstractType
{
    const NAME = 'billingAccountType';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'nickname',
            'text',
            array(
                'mapped' => true,
                'label' => 'checkout.account_nickname',
                'attr' => array(
                    'data-bind' => 'value: nickname'
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('create_billing', 'edit_billing'),
                            'message' => 'checkout.error.account_nickname.empty',
                        )
                    ),
                ),
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
                            'groups' => array('create_billing'),
                            'message' => 'checkout.error.payor_name.empty',
                        )
                    ),
                    new Regex(
                        array(
                            'groups' => array('create_billing'),
                            'message' => 'checkout.error.payor_name.invalid',
                            'pattern' => '/^(\w+\s){1,2}\w+$/'
                        )
                    )
                ),
                'attr' => array(
                    'placeholder' => 'checkout.payor_name.placeholder',
                    'data-bind' => 'value: payerName',
                    'row_attr' => array(
                        'data-bind' => 'visible: $parent.isCreateMode'
                    )
                )
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
                'attr' => array(
                    'data-bind' => 'value: accountNumber',
                    'row_attr' => array(
                        'data-bind' => 'visible: $parent.isCreateMode'
                    )
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('create_billing'),
                            'message' => 'checkout.error.account_number.empty',
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'RoutingNumber',
            'text',
            array(
                'mapped' => false,
                'label' => 'checkout.routing_number',
                'attr' => array(
                    'data-bind' => 'value: routingNumber',
                    'row_attr' => array(
                        'data-bind' => 'visible: $parent.isCreateMode'
                    )
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('create_billing'),
                            'message' => 'checkout.error.routing_number.empty',
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'ACHDepositType',
            'choice',
            array(
                'mapped' => false,
                'label' => 'checkout.account_type',
                'attr' => array(
                    'data-bind' => 'checked: accountType',
                    'row_attr' => array(
                        'data-bind' => 'visible: $parent.isCreateMode'
                    )
                ),
                'expanded' => true,
                'choices' => array(
                    ACHDepositType::CHECKING => 'checkout.account_type.checking',
                    ACHDepositType::SAVINGS => 'checkout.account_type.savings',
                    ACHDepositType::BUSINESS_CHECKING => 'checkout.account_type.business_checking'
                ),
                'empty_value'  => false,
                'invalid_message' => 'checkout.error.account_type.invalid',
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('create_billing'),
                            'message' => 'checkout.error.account_type.empty',
                        )
                    ),
                ),
            )
        );
        $builder->add(
            'isActive',
            'checkbox',
            array(
                'label' => 'settings.payment_account.make_active',
                'attr' => array(
                    'data-bind' => 'checked: isActive, disable: !allowActive',
                    'row_attr' => array(
                        'data-bind' => 'visible: allowActive',
                        'class' => 'payment-account-checkbox',
                    )
                ),
            )
        );

        $builder->add(
            'isFakeActive',
            'checkbox',
            array(
                'label' => 'settings.payment_account.current_active',
                'mapped' => false,
                'attr' => array(
                    'data-bind' => 'checked: isActive, disable: !allowActive',
                    'class' => 'payment-account-checkbox',
                    'row_attr' => array(
                        'data-bind' => 'visible: !allowActive',
                        'class' => 'payment-account-checkbox',
                    )
                ),
            )
        );
        $builder->add(
            'id',
            'hidden',
            array(
                'mapped' => false,
                'attr' => array(
                    'data-bind' => 'value: id'
                ),
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'RentJeeves\DataBundle\Entity\BillingAccount',
                'validation_groups' => function (FormInterface $form) {
                    $data = $form->getData();
                    if ($data->getId()) {
                        return array('edit_billing');
                    } else {
                        return array('create_billing');
                    }
                }
            )
        );
    }

    public function getName()
    {
        return static::NAME;
    }
}
