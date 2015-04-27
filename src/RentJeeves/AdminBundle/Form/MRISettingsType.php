<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("form.mri_settings")
 */
class MRISettingsType extends Base
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url');
        $builder->add('user');
        $builder->add('password');
        $builder->add('databaseName');
        $builder->add('partnerKey');
        $builder->add('hash');
        $builder->add('siteId');
        $builder->add('chargeCode');
        $builder->add('clientId');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\MRISettings'
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_mri_settings';
    }
}
