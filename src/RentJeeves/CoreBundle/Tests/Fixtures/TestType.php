<?php
namespace RentJeeves\CoreBundle\Tests\Fixtures;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'field1',
            'text',
            array(
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'field1.empty',
                        )
                    ),
                ),
            )
        );
        $builder->add(
            'field2',
            'text',
            array(
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'field2.empty1',
                        )
                    ),
                    new NotBlank(
                        array(
                            'message' => 'field2.empty2',
                        )
                    ),
                ),
            )
        );

        $builder->add(
            'child',
            new SubTestType(),
            array(
                'error_bubbling' => true,
            )
        );
    }

    public function getName()
    {
        return 'form';
    }
}
