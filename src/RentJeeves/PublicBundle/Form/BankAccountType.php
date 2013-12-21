<?php

namespace RentJeeves\PublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Payum\Heartland\Soap\Base\ACHDepositType;

class BankAccountType extends AbstractType
{
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
                            'message' => 'checkout.error.account_nickname.empty',
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
                'attr' => array(
                    'data-bind' => 'value: accountNumber'
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
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
                    'data-bind' => 'value: routingNumber'
                ),
                'constraints' => array(
                    new NotBlank(
                        array(
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
                    'data-bind' => 'checked: accountType'
                ),
                'expanded' => true,
                'choices' => array(
                    ACHDepositType::CHECKING => 'checkout.account_type.checking',
                    ACHDepositType::SAVINGS => 'checkout.account_type.savings',
                    ACHDepositType::UNASSIGNED => 'checkout.account_type.business_checking'
                ),
                'empty_value'  => false,
                'invalid_message' => 'checkout.error.account_type.invalid',
                'constraints' => array(
                    new NotBlank(
                        array(
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
                'label' => 'settings.payment_account.active',
                'attr' => array(
                    'data-bind' => 'checked: isActive'
                ),
            )
        );
        $builder->add(
            'id',
            'hidden',
            array(
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
            )
        );
    }

    public function getName()
    {
        return 'directDepositType';
    }
}
