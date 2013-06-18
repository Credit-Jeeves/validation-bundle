<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderAuthorizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', new UserType())
            ->add('authorize', new AuthorizeNetAimType());
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\Order',
                'translation_domain' => 'checkout',
                'validation_groups' => array('buy_report'),
                'cascade_validation' => true,
            )
        );
    }

    public function getName()
    {
        return 'order_authorize';
    }
}
