<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\CheckoutBundle\Form\Type\UserType;
use CreditJeeves\CoreBundle\Form\Widget\MonthYearType;
use CreditJeeves\DataBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class AuthorizeNetAimType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'card_num',
                'text',
                array(
                    'error_bubbling' => true,
                    'label' => 'card_num',
                    'constraints' => array(
                        new NotBlank(
                            array(
                                'groups' => array('buy_report_new'),
                                'message' => 'error.card_num.empty',
                            )
                        ),
                    )
                )
            )
            ->add(
                'card_code',
                'text',
                array(
                    'error_bubbling' => true,
                    'label' => 'card_code',
                    'constraints' => array(
                        new NotBlank(
                            array(
                                'groups' => array('buy_report_new'),
                                'message' => 'error.card_code.empty',
                            )
                        ),
                    )
                )
            )
            ->add(
                'exp_date',
                new MonthYearType(),
                array(
                    'error_bubbling' => true,
                    'label' => 'exp_date',
                    'input' => 'string',
                    'format' => 'MMyyyy-d',
                    'years' => range(date('Y'), date('Y') + 12),
                    'months' => range(1, 12),
                    'days' => array(1),
                    'invalid_message' => 'error.exp_date.valid',
                    'empty_value' => array(
                        'year' => 'Year',
                        'month' => 'Month',
                        'day' => 1,
                    ),
                    'constraints' => array(
                        new Valid(
                        ),
                        new NotBlank(
                            array(
                                'groups' => array('buy_report_new'),
                                'message' => 'error.exp_date.empty',
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
                'data_class' => 'CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim',
                'validation_groups' => array(),
                'translation_domain' => 'checkout'
            )
        );
    }

    public function getName()
    {
        return 'authorize_net_aim_type';
    }
}
