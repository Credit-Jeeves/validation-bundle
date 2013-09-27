<?php
namespace RentJeeves\CoreBundle\Tests\Controller\Traits;

use RentJeeves\CoreBundle\Tests\Fixtures\TestType;
use RentJeeves\TestBundle\BaseTestCase;
use CreditJeeves\TestBundle\Mocks;
use Symfony\Component\Form\AbstractType;

/**
 * @author Ton Sharp <66Ton99@gmail.com>
 */
class FormErrorsCase extends BaseTestCase
{
    use Mocks\Translator;

    protected function getFormTypeErrors($formType)
    {
        $controller = $this->getMock(
            'RentJeeves\CoreBundle\Tests\Fixtures\TestController',
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
