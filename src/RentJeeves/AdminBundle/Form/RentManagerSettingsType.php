<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("form.rent_manager_settings")
 */
class RentManagerSettingsType extends Base
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('corpid');
        $builder->add('user');
        $builder->add('password');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\RentManagerSettings'
            ]
        );
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_rent_manager_settings';
    }
}
