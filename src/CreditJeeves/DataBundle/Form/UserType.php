<?php

namespace CreditJeeves\DataBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('usernameCanonical')
            ->add('email')
            ->add('emailCanonical')
            ->add('enabled')
            ->add('salt')
            ->add('password')
            ->add('lastLogin')
            ->add('locked')
            ->add('expired')
            ->add('expiresAt')
            ->add('confirmationToken')
            ->add('passwordRequestedAt')
            ->add('roles')
            ->add('credentialsExpired')
            ->add('credentialsExpireAt')
            ->add('first_name')
            ->add('middle_initial')
            ->add('last_name')
            ->add('street_address1')
            ->add('street_address2')
            ->add('unit_no')
            ->add('city')
            ->add('state')
            ->add('zip')
            ->add('phone_type')
            ->add('phone')
            ->add('emailNotification')
            ->add('offer_notification')
            ->add('date_of_birth')
            ->add('ssn')
            ->add('type')
            ->add('is_active')
            ->add('has_data')
            ->add('has_report')
            ->add('dealer_groups')
            ->add('vehicle');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'CreditJeeves\DataBundle\Entity\User'));
    }

    public function getName()
    {
        return 'creditjeeves_databundle_usertype';
    }
}
