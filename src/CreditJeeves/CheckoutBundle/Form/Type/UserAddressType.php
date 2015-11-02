<?php
namespace CreditJeeves\CheckoutBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use CreditJeeves\ApplicantBundle\Form\Type\UserAddressType as Base;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserAddressType extends Base
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('unit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'CreditJeeves\DataBundle\Entity\MailingAddress',
                'validation_groups' => array('buy_report_new'),
            )
        );
    }
}
