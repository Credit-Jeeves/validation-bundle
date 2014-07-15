<?php
namespace RentJeeves\AdminBundle\Form;

use Symfony\Component\Form\AbstractType as Base;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use RentJeeves\DataBundle\Entity\GroupSettings as GroupSetting;

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

        $builder->add(
            'isPayBalanceOnly',
            'checkbox',
            array(
                'error_bubbling'    => true,
                'label'             => 'is.pay.balance.only',
                'required'          => false,
            )
        );

        $dueDate = array();
        foreach (range(1, 31, 1) as $key => $value) {
            $dueDate[$value] = $value;
        }

        $builder->add(
            'dueDate',
            'choice',
            array(
                'choices'           => $dueDate,
                'error_bubbling'    => true,
                'label'             => 'common.default.due_date',
                'required'          => true,
                'empty_data'        => 1,
            )
        );

        $builder->add(
            'openDate',
            'choice',
            array(
                'choices'           => $dueDate,
                'error_bubbling'    => true,
                'label'             => 'common.default.open_date',
                'required'          => true,
                'empty_data'        => 1,
            )
        );

        $builder->add(
            'closeDate',
            'choice',
            array(
                'choices'           => $dueDate,
                'error_bubbling'    => true,
                'label'             => 'common.default.close_date',
                'required'          => true,
                'empty_data'        => 31,
            )
        );

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                // this would be entity, i.e. GroupSettings
                /**
                 * @var $groupSettings GroupSetting
                 */
                $groupSettings = $event->getData();

                if (!$groupSettings->getIsIntegrated() && $groupSettings->getIsPayBalanceOnly()) {
                    $form->get('isPayBalanceOnly')->addError(
                        new FormError('is.pay.balance.only.error')
                    );
                }
            }
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
