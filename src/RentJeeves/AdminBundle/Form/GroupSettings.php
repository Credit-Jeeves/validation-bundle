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

        $dueDate = array();
        foreach (range(1, 31, 1) as $key => $value) {
            $key++;
            $dueDate[$key] = $value;
        }

        $builder->add(
            'dueDate',
            'choice',
            array(
                'choices'           => $dueDate,
                'error_bubbling'    => true,
                'label'             => 'due_date',
                'required'          => false,
                'empty_data'        => 1,
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
