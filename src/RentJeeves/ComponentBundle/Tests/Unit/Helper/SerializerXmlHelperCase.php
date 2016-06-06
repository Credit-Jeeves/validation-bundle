<?php
namespace RentJeeves\ComponentBundle\Tests\Unit\Helper;

use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class SerializerXmlHelperCase extends UnitTestBase
{
    /**
     * @test
     */
    public function shouldGetStandartXmlHeader()
    {
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8"?>',
            SerializerXmlHelper::getStandartXmlHeader()
        );
    }

    /**
     * @test
     */
    public function shouldReturnContext()
    {
        $this->assertInstanceOf(
            'JMS\Serializer\SerializationContext',
            SerializerXmlHelper::getSerializerContext(["MRI"])
        );
    }

    /**
     * @test
     */
    public function shouldRemoveStandartXmlHeader()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><var></var>';
        $this->assertEquals(
            '<var></var>',
            SerializerXmlHelper::removeStandartHeaderXml($xml)
        );
    }
}
