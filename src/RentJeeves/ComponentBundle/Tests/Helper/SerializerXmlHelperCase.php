<?php
namespace RentJeeves\ComponentBundle\Tests\Helper;

use RentJeeves\ComponentBundle\Helper\SerializerXmlHelper;
use RentJeeves\TestBundle\BaseTestCase;

class SerializerXmlHelperCase extends BaseTestCase
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
