<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use RentJeeves\CoreBundle\Services\PropertyManager;
use RentJeeves\TestBundle\Tests\Unit\UnitTestBase;

class PropertyManagerCase extends UnitTestBase
{

    /**
     * @test
     */
    public function shouldGenerateInvalidPropertyAddressIndex()
    {
        $index = PropertyManager::generateInvalidAddressIndex("3217", "S. Babcock St", "Melbourne", "FL");
        $this->assertEquals(
            "3217sbabcockstmelbourneflinvalidaddress",
            $index,
            'Invalid property index is not being generated correctly'
        );
    }
}
