<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InviteTenantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('first_name');
        $builder->add('last_name');
        $builder->add('email');
        $builder->add('phone');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'CreditJeeves\DataBundle\Entity\Tenant',
                'validation_groups' => array(
                    'tenant_invite',
                ),
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_publicbundle_invitetype';
    }
}
