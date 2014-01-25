<?php

namespace RentJeeves\PublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Payum\Heartland\Soap\Base\ACHDepositType;
use Symfony\Component\Validator\Constraints\Regex;

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
                            'message' => 'checkout.error.payor_name.invalid',
                            'pattern' => '/^(\w+\s){1,2}\w+$/'
                        )
                    )
                ),
                'attr' => array(
                    'placeholder' => 'checkout.payor_name.placeholder'
                )
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
                'expanded' => true,
                'choices' => array(
                    ACHDepositType::CHECKING => 'checkout.account_type.checking',
                    ACHDepositType::SAVINGS => 'checkout.account_type.savings',
                    ACHDepositType::UNASSIGNED => 'checkout.account_type.business_checking'
                ),
                'empty_value' => false,
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
