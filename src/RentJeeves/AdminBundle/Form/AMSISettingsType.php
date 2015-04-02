<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\FormType;

/**
 * @Service("form.amsi_settings")
 * @FormType("amsiSettings")
 */
class AMSISettingsType extends Base
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url');
        $builder->add('user');
        $builder->add('password');
        $builder->add('portfolioName');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\AMSISettings'
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'amsiSettings';
    }
}
