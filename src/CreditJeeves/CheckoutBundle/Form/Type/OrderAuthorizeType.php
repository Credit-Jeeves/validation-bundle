<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderAuthorizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('user', new UserType());
        $builder->add(
            'authorizes',
            'collection',
            array(
                'type' => new AuthorizeNetAimType(),
                'by_reference' => true,
                'allow_add' => true,
                'error_bubbling' => true,
                'empty_data' => true,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'groups' => array('buy_report_new'),
                            'message' => 'authorizes.empty',
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
                'cascade_validation' => true,
                'data_class' => 'CreditJeeves\DataBundle\Entity\Order',
                'validation_groups' => array('buy_report_new'),
            )
        );
    }

    public function getName()
    {
        return 'order_authorize';
    }
}
