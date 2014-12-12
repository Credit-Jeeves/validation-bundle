<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * Class YardiSettings
 * @Service("form.resman_settings")
 */
class ResManSettings extends Base
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('accountId');
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
