<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\CheckoutBundle\Form\Type\UserType;
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
        $months = array_combine(
            array('') + range(0, 12),
            array('Month') + range(0, 12)
        );
        $years = array_combine(
            array('') + range(date('y') - 1, date('y') + 12),
            array('Year') + range(date('Y') - 1, date('Y') + 12)
        );


        $builder
            ->add(
                'card_number',
                'text',
                array(
                    'constraints' => array(
                        new NotBlank(),
                    )
                )
            )
            ->add(
                'card_csc',
                'text',
                array(
                    'constraints' => array(
                        new NotBlank(),
                    )
                )
            )
            ->add(
                'card_expiration_date_month',
                'choice',
                array(
                    'choices' => $months,
                    'constraints' => array(
                        new NotBlank(),
                    )
                )
            )
            ->add(
                'card_expiration_date_year',
                'choice',
                array(
                    'label'  => false,
                    'choices' => $years,
                    'constraints' => array(
                        new NotBlank(),
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
            )
        );
    }

    public function getName()
    {
        return 'authorize_net_aim_type';
    }
}
