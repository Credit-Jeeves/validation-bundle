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

class AuthorizeNetAimType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'card_num',
                'text',
                array(
                    'constraints' => array(
                        new NotBlank(array('groups' => array('buy_report_new'))),
                    )
                )
            )
            ->add(
                'card_code',
                'text',
                array(
                    'constraints' => array(
                        new NotBlank(array('groups' => array('buy_report_new'))),
                    )
                )
            )
            ->add(
                'exp_date',
                new MonthYearType(),
                array(
                    'input' => 'string',
                    'format' => 'MMyyyy-d',
                    'years' => range(date('Y'), date('Y') + 12),
                    'months' => range(1, 12),
                    'days' => array(1),
                    'empty_value' => array(
                        'year' => 'Year',
                        'month' => 'Month',
                        'day' => 1,
                    ),
                    'constraints' => array(
                        new NotBlank(array('groups' => array('buy_report_new'))),
                    )
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                'data_class' => 'CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim',
            )
        );
    }

    public function getName()
    {
        return 'authorize_net_aim_type';
    }
}
