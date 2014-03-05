<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupSettings extends Base
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'isPidVerificationSkipped',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'is.pid.verification.skip',
                'required'          => false,
            )
        );

        $builder->add(
            'isIntegrated',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'is.integrated',
                'required'          => false,
            )
        );

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\GroupSettings'
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_group_settings';
    }
}
