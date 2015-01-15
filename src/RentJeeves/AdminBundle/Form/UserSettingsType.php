<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserSettingsType extends Base
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'isBaseOrderReport',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'base.order.report',
                'required'          => false,
            )
        );

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\UserSettings'
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_user_settings';
    }
}
