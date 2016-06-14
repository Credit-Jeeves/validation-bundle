<?php

namespace RentJeeves\ApiBundle\Tests\Unit\Services\ResourceUrlGenerator;

use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlResourceMeta;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\ResourceUrlGenerator;
use RentJeeves\ApiBundle\Tests\Data\Services\ResourceUrlGenerator\TestResource;
use RentJeeves\TestBundle\BaseTestCase;

class ResourceUrlGeneratorCase extends BaseTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metaReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $encoderFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;

    /**
     * Test setup.
     */
    public function setup()
    {
        $this->metaReader = $this
            ->getMockBuilder('RentJeeves\ApiBundle\Services\ResourceUrlGenerator\ResourceUrlMetaReader')
            ->disableOriginalConstructor()
            ->getMock();

        $annotation = new UrlResourceMeta(
            [
                'prefix' => '',
                'actionName' => 'test'
            ]
        );

        $this->metaReader
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue($annotation));

        $this->encoderFactory = $this
            ->getMockBuilder('RentJeeves\ApiBundle\Services\Encoders\EncoderFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->encoderFactory
            ->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue(null));

        $this->router = $this
            ->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->router
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue(true));

        $this->resource = $this
            ->getMock('RentJeeves\ApiBundle\Tests\Data\Services\ResourceUrlGenerator\TestResource');
    }

    public function getResourceUrlGenerator()
    {
        return new ResourceUrlGenerator($this->router, $this->encoderFactory, $this->metaReader);
    }

    /**
     * @test
     */
    public function generateWithMocks()
    {
        $this->assertTrue($this->getResourceUrlGenerator()->generate($this->resource));
    }

    /**
     * @test
     */
    public function generate()
    {
        $resource = new TestResource();

        $url = $this->getContainer()->get('api.resource_url_generator')->generate($resource);

        $this->assertEquals($url, 'http://localhost/rj_test.php/login?id=1');
    }
}
