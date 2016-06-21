<?php

namespace RentJeeves\ApiBundle\Tests\Unit\Request;

use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Request\Annotation\QueryParam;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;
use RentJeeves\ApiBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\TestBundle\BaseTestCase;

class ParamFetcherCase extends BaseTestCase
{
    /**
     * @var array
     */
    private $controller;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paramReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $violationFormatter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $encoderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $constraint;

    /**
     * Test setup.
     */
    public function setup()
    {
        $this->controller = array(new \stdClass(), 'indexAction');

        $this->paramReader = $this->getMockBuilder('FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->constraint = $this->getMockForAbstractClass('Symfony\Component\Validator\Constraint');

        $annotations = array();
        $annotations['foo'] = new AttributeParam;
        $annotations['foo']->name = 'foo';
        $annotations['foo']->requirements = '\d+';
        $annotations['foo']->nullable = false;
        $annotations['foo']->encoder = 'skip32.id_encoder';

        $annotations['fot'] = new AttributeParam;
        $annotations['fot']->name = 'foo';

        $annotations['bar'] = new RequestParam;
        $annotations['bar']->name = 'bar';
        $annotations['bar']->requirements = '\d+';
        $annotations['bar']->encoder = 'skip32.id_encoder';

        $annotations['baz'] = new QueryParam;
        $annotations['baz']->name = 'baz';
        $annotations['baz']->requirements = '\d?';
        $annotations['baz']->encoder = ['skip32.id_encoder', "skipNotValid" => true];

        $annotations['baf'] = new QueryParam;
        $annotations['baf']->name = 'baf';
        $annotations['baf']->encoder = 'skip32.id_encoder';

        $this->paramReader
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue($annotations));

        $this->validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $this->violationFormatter = $this->getMock('FOS\RestBundle\Util\ViolationFormatterInterface');

        $this->encoderFactory = $this->getContainer()->get('encoder_factory');
    }

    /**
     * Get a param fetcher.
     *
     * @param array $query      Query parameters for the request.
     * @param array $request    Request parameters for the request.
     * @param array $attributes Attributes for the request.
     *
     * @return ParamFetcher
     */
    public function getParamFetcher($query = array(), $request = array(), $attributes = null)
    {
        $attributes = $attributes ?: array('_controller' => __CLASS__.'::stubAction');

        $this->request = new Request($query, $request, $attributes);

        return new ParamFetcher(
            $this->encoderFactory,
            $this->paramReader,
            $this->request,
            $this->violationFormatter,
            $this->validator
        );
    }

    /**
     * Test valid parameters.
     * @test
     *
     * @param $queryParams
     * @param $requestParams
     * @param $attributeParams
     * @param $expectedParams
     * @param $expectedAttributes
     *
     * @dataProvider configuredParamDataProvider
     */
    public function configuredParam(
        $queryParams,
        $requestParams,
        $attributeParams,
        $expectedParams,
        $expectedAttributes
    ) {
        $queryFetcher = $this->getParamFetcher($queryParams, $requestParams, $attributeParams);
        $queryFetcher->setController($this->controller);

        $this->assertEquals($expectedParams, $queryFetcher->all());
        $this->assertEquals($expectedAttributes, $this->request->attributes->all());
    }

    /**
     * Data provider for the valid parameters test.
     *
     * @return array Data
     */
    public static function configuredParamDataProvider()
    {
        return [
            [
                ['baz' => 656765400],
                ['bar' => 656765400],
                ['foo' => 656765400, 'fot' => 656765400],
                ['baz' =>1, 'bar' => 1, 'baf' => null],
                ['foo' => 1, 'fot' => 656765400]
            ],
            [
                ['baz' => ''],
                ['bar' => 656765400],
                ['foo' => 656765400, 'fot' => 'test'],
                ['bar' => 1, 'baz' => '', 'baf' => null],
                ['foo' => 1, 'fot' => 'test']
            ],
        ];
    }

    /**
     * @test
     * @expectedException        \RentJeeves\ApiBundle\Services\Encoders\ValidationEncoderException
     * @expectedExceptionMessage Value "TEST" isn't correct encrypted Id.
     */
    public function exceptionNotValidDecryptionValue()
    {
        $queryFetcher = $this->getParamFetcher(['baf' => 'TEST']);
        $queryFetcher->setController($this->controller);

        $queryFetcher->get('baf');
    }
}
