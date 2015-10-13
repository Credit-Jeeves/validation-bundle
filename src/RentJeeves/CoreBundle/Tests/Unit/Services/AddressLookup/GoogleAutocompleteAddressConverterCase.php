<?php
namespace RentJeeves\CoreBundle\Tests\Unit\Services;

use RentJeeves\TestBundle\Functional\BaseTestCase;

class GoogleAutocompleteAddressConverterCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldConvertToAddressIfJsonValidAndHasAllRequiredFields()
    {
        $json = '{"address":[{"long_name":"13","short_name":"13","types":["street_number"]},
            {"long_name":"Greenwich Street","short_name":"Greenwich St","types":["route"]},
            {"long_name":"Lower Manhattan","short_name":"Lower Manhattan","types":["neighborhood","political"]},
            {"long_name":"Manhattan","short_name":"Manhattan",
            "types":["sublocality_level_1","sublocality","political"]},
            {"long_name":"New York","short_name":"NY","types":["locality","political"]},
            {"long_name":"New York County","short_name":"New York County",
            "types":["administrative_area_level_2","political"]},{"long_name":"New York","short_name":"NY",
            "types":["administrative_area_level_1","political"]},{"long_name":"United States","short_name":"US",
            "types":["country","political"]},{"long_name":"10013","short_name":"10013","types":["postal_code"]}],
            "geometry":{"location":{"J":40.7218084,"M":-74.00973160000001}}}';

        $result = $this->getGoogleAutocompleteAddressConverter()->convert($json);

        $this->assertInstanceOf('\RentJeeves\CoreBundle\Services\AddressLookup\Model\Address', $result);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfJsonIsNotValid()
    {
        $json = '{"address"::[{';

        $this->getGoogleAutocompleteAddressConverter()->convert($json);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfJsonDoesntHaveGoogleLocation()
    {
        $json = '{"address":[{"long_name":"13","short_name":"13","types":["street_number"]},
            {"long_name":"Greenwich Street","short_name":"Greenwich St","types":["route"]},
            {"long_name":"Lower Manhattan","short_name":"Lower Manhattan","types":["neighborhood","political"]},
            {"long_name":"Manhattan","short_name":"Manhattan",
            "types":["sublocality_level_1","sublocality","political"]},
            {"long_name":"New York","short_name":"NY","types":["locality","political"]},
            {"long_name":"New York County","short_name":"New York County",
            "types":["administrative_area_level_2","political"]},{"long_name":"New York","short_name":"NY",
            "types":["administrative_area_level_1","political"]},{"long_name":"United States","short_name":"US",
            "types":["country","political"]},{"long_name":"10013","short_name":"10013","types":["postal_code"]}],
            }';

        $this->getGoogleAutocompleteAddressConverter()->convert($json);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldThrowExceptionIfJsonDoesntHaveZipCode()
    {
        $json = '{"address":[{"long_name":"13","short_name":"13","types":["street_number"]},
            {"long_name":"Greenwich Street","short_name":"Greenwich St","types":["route"]},
            {"long_name":"Lower Manhattan","short_name":"Lower Manhattan","types":["neighborhood","political"]},
            {"long_name":"Manhattan","short_name":"Manhattan",
            "types":["sublocality_level_1","sublocality","political"]},
            {"long_name":"New York","short_name":"NY","types":["locality","political"]},
            {"long_name":"New York County","short_name":"New York County",
            "types":["administrative_area_level_2","political"]},{"long_name":"New York","short_name":"NY",
            "types":["administrative_area_level_1","political"]},{"long_name":"United States","short_name":"US",
            "types":["country","political"]}],
            "geometry":{"location":{"J":40.7218084,"M":-74.00973160000001}}}';

        $this->getGoogleAutocompleteAddressConverter()->convert($json);
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\AddressLookup\GoogleAutocompleteAddressConverter
     */
    protected function getGoogleAutocompleteAddressConverter()
    {
        return $this->getContainer()->get('google_autocomplete_address_converter');
    }
}
