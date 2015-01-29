<?php
namespace RentJeeves\AdminBundle\Form;

use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("form.accounting_settings")
 */
class AccountingSettingsType extends Base
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'apiIntegration',
            'choice',
            array(
                'error_bubbling'    => true,
                'choices'           => array_map(
                    'ucwords',
                    ApiIntegrationType::cachedTitles()
                )
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\AccountingSettings'
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_accounting_settings';
    }
}
