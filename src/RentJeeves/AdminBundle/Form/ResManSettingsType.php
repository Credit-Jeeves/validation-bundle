<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("form.resman_settings")
 */
class ResManSettingsType extends Base
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('accountId');
        $builder->add(
            'syncBalance',
            'checkbox',
            [
                'error_bubbling'    => true,
                'label'             => 'common.sync_balance.label',
                'required'          => false,
            ]
        );
        $builder->add('url', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\ResManSettings'
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_resman_settings';
    }
}
