<?php

namespace RentJeeves\LandlordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InviteTenantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            null,
            array(
                'error_bubbling'    => true,
            )
        );
        $builder->add(
            'last_name',
            null,
            array(
                'error_bubbling'    => true,
            )
        );
        $builder->add(
            'email',
            null,
            array(
                'error_bubbling'    => true,
            )
        );
        $builder->add(
            'phone',
            null,
            array(
                'error_bubbling'    => true,
            )
        );
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
