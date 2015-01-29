<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Enum\PaymentTypeACH;
use RentJeeves\DataBundle\Enum\PaymentTypeCC;

/**
 * @Service("form.yardi_settings")
 */
class YardiSettingsType extends Base
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('url');
        $builder->add('username');
        $builder->add('password');
        $builder->add('databaseServer');
        $builder->add('databaseName');
        $builder->add('platform');
        $builder->add('databaseName');
        $builder->add(
            'syncBalance',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'yardi.sync_balance.label',
                'required'          => false,
            )
        );
        $builder->add(
            'paymentTypeACH',
            'choice',
            array(
                'error_bubbling'    => true,
                'choices'           => array_map(
                    'strtoupper',
                    array_change_key_case(
                        PaymentTypeACH::all(),
                        CASE_LOWER
                    )
                ),
                'label'             => 'common.payment_type_ach',
            )
        );

        $builder->add(
            'notesACH',
            null,
            array(
                'error_bubbling'    => true,
                'label'             => 'common.payment_type_ach.notes',
            )
        );

        $builder->add(
            'paymentTypeCC',
            'choice',
            array(
                'error_bubbling'    => true,
                'choices'           => array_map(
                    'strtoupper',
                    array_change_key_case(
                        PaymentTypeCC::all(),
                        CASE_LOWER
                    )
                ),
                'label'             => 'common.payment_type_cc',
            )
        );

        $builder->add(
            'notesCC',
            null,
            array(
                'error_bubbling'    => true,
                'label'             => 'common.payment_type_cc.notes',
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation'    => true,
                'data_class'            => 'RentJeeves\DataBundle\Entity\YardiSettings'
            )
        );
    }

    public function getName()
    {
        return 'rentjeeves_adminbundle_yardi_settings';
    }
}
