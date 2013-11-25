<?php

namespace RentJeeves\PublicBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            null,
            array(
                'label'     => 'Landlord Name',
                'required'  => false
            )
        );
        $builder->add(
            'last_name',
             null,
             array(
                 'required'  => false
             )
        );
        $builder->add(
            'email',
            null,
            array(
                'label' => 'Email*',
            )
        );
        $builder->add(
            'phone',
            null
        );
        $builder->add(
            'unit',
            null
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'RentJeeves\DataBundle\Entity\Invite',
                'validation_groups' => array(
                    'invite',
                ),
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_publicbundle_invitetype';
    }
}
