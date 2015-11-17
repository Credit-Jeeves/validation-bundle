<?php
namespace RentJeeves\CheckoutBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use CreditJeeves\ApplicantBundle\Form\Type\UserAddressType as Base;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserAddressType extends Base
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        parent::buildForm($builder, $options);
        $builder->remove('unit');
        $builder->add(
            'street',
            'text',
            array(
                'label' => false,
                'attr' => array(
                    'class' => 'all-width',
                    'placeholder' => 'common.street',
                    'data-bind' => "value: {$this->koPrefix}address.street"
                )
            )
        );

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'CreditJeeves\DataBundle\Entity\MailingAddress'
            )
        );
    }
}
