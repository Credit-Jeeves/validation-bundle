<?php
namespace CreditJeeves\ApplicantBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


class NotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'score_changed_notification',
            'hidden',
            array(
                'required'  => false,
                )
            );
        $builder->add(
            'offer_notification',
            'hidden',
            array(
                'required'  => false,
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CreditJeeves\DataBundle\Entity\User',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'username',
        ));
    }

    public function getName()
    {
        return 'notification';
    }
}