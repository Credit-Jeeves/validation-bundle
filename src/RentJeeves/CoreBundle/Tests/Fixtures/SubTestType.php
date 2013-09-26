<?php
namespace RentJeeves\CoreBundle\Tests\Fixtures;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SubTestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'field1',
            'text',
            array(
                'mapped' => false,
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'field1.empty',
                        )
                    ),
                ),
            )
        );
        $builder->add('name');

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'cascade_validation' => true,
                'data_class' => 'RentJeeves\CoreBundle\Tests\Fixtures\TestEntity'
            )
        );
    }

    public function getName()
    {
        return 'sub';
    }
}
