<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use CreditJeeves\ApplicantBundle\Form\Type\SsnType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;

class UserDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'date_of_birth',
            'date',
            array(
                'error_bubbling' => true,
                'label' => 'common.date_of_birth',
                'format' => 'MMddyyyy',
                'years' => range(date('Y') - 110, date('Y')),
//                'empty_value' => array( // TODO fix validation problem (error message processing)
//                    'year' => 'Year',
//                    'month' => 'Month',
//                    'day' => 'Day',
//                ),
                'attr' => array(
                    'class' => 'original',
                    'html' => '<div class="tooltip-box type3 pie-el">' .
                        '<p class="verify" data-bind="i18n: {}">checkout.date_of_birth.tooltip.line1</p>' .
                        '<p class="verify" data-bind="i18n: {}">checkout.date_of_birth.tooltip.line2</p>' .
                    '</div>'
                )
            )
        );
        $builder->add(
            'ssn',
            new SsnType(),
            array(
                'error_bubbling' => true,
                'label' => 'common.ssn',
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'RentJeeves\DataBundle\Entity\Tenant',
                'validation_groups' => array(
                    'birth_and_ssn',
                ),
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_checkoutbundle_userdetailstype';
    }
}
