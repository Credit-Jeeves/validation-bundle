<?php
namespace RentJeeves\CoreBundle\Tests\Controller\Traits;

use RentJeeves\TestBundle\BaseTestCase;
use CreditJeeves\TestBundle\Mocks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class FormErrorsCase extends BaseTestCase
{
    use Mocks\Translator;

    protected function getFormTypeErrors($formType)
    {
        $controller = $this->getMock(
            'RentJeeves\CoreBundle\Tests\Controller\Traits\TestController',
            array('get')
        );
        $controller->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->getTranslator()));

        $formType = $this->getContainer()->get('form.factory')->create($formType);
        $formType->submit(array());
        return $this->callNoPublicMethod(
            $controller,
            'getFormErrors',
            array($formType)
        );
    }

    /**
     * @test
     */
    public function getFormErrors()
    {
        $testTypeErrors = $this->getFormTypeErrors(new TestType());

        $this->assertArrayHasKey('form', $testTypeErrors);
        $this->assertEquals(
            array(
                '_globals' => array(
                    'The CSRF token is invalid. Please try to resubmit the form.'
                ),
                'form_field1' => array(
                    'field1.empty'
                ),
                'form_field2' => array(
                    'field2.empty1',
                    'field2.empty2'
                ),
                'form_child' => array(
                    'form_child_field1' => array(
                        'field1.empty'
                    ),
                    'form_child_name' => array(
                        'name.empty'
                    )
                )
            ),
            $testTypeErrors['form']
        );
    }
}

class TestController
{
    use \RentJeeves\CoreBundle\Controller\Traits\FormErrors;
}

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 *
 * @ORM\Table(name="test")
 * @ORM\Entity()
 */
class TestEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     * @Assert\NotBlank(
     *     message="name.empty"
     * )
     */
    public $name;
}

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
                'data_class' => 'RentJeeves\CoreBundle\Tests\Controller\Traits\TestEntity'
            )
        );
    }

    public function getName()
    {
        return 'sub';
    }
}

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
