<?php

namespace RentJeeves\ApiBundle\Tests\Services\ResourceUrlGenerator;

use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\Annotation\UrlGenerateMeta;
use RentJeeves\ApiBundle\Services\ResourceUrlGenerator\ResourceUrlGenerator;
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
            ->getMockBuilder('RentJeeves\ApiBundle\Services\ResourceUrlGenerator\UrlGenerateMetaReader')
            ->disableOriginalConstructor()
            ->getMock();

        $annotation = new UrlGenerateMeta(
            [
                'prefix' => '',
                'actionName' => 'test'
            ]
        );

        $this->metaReader
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue($annotation));

        $this->router = $this
            ->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->router
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue(true));

        $this->resource = $this->getMock('RentJeeves\ApiBundle\Tests\Services\ResourceUrlGenerator\TestResource');

        $this->resource
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
    }

    public function getResourceUrlGenerator()
    {
        return new ResourceUrlGenerator($this->router, $this->getContainer(), $this->metaReader);
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

        $this->assertEquals($url, '/rj_test.php/login?id=1');
    }
}
