<?php

namespace RentJeeves\LandlordBundle\Tests\Unit;

use RentJeeves\TestBundle\Functional\BaseTestCase;
use RentJeeves\LandlordBundle\Registration\MerchantAccountModel;
use RentJeeves\LandlordBundle\Registration\SAMLEnvelope;
use \DOMDocument;

class SAMLEnvelopeCase extends BaseTestCase
{
    protected static $user;

    public function setUp()
    {
        if (!self::$user) {
            $this->load(true);
            //@TODO Its hack, becouse after use load function, for load fixtures, we have problem.
            static::$kernel = null;
            //end hack
            $container = static::getContainer();
            $em = $container->get('doctrine.orm.entity_manager');

            self::$user = $em->getRepository('RjDataBundle:Landlord')->findOneBy(
                array(
                    'email' => 'landlord1@example.com',
                )
            );
        }
    }

    /**
     * @test
     */
    public function shouldCreatePortalApplicationOnConstruct()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $this->assertInstanceOf('DomDocument', $saml->getPortalApplication());
    }

    /**
     * @test
     */
    public function shouldCreateAssertionResponseOnConstruct()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $this->assertInstanceOf('DomDocument', $saml->getAssertionResponse());
    }

    /**
     * @test
     */
    public function shouldUseBase64ToEncodePortalApplication()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $expected = '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
            '<InboundConfiguration><InboundApplicationConfigurations><InboundApplicationConfiguration>' .
            '<MerchantDbaName>TIMOTHY APPLEGATE</MerchantDbaName><MerchantEmail>landlord1@example.com</MerchantEmail>' .
            '<MerchantPhone>7858655392</MerchantPhone><CorporateName>TIMOTHY APPLEGATE</CorporateName>'.
            '<CorporatePhone>7858655392</CorporatePhone>' .
            '<MerchantPrimaryContactName>TIMOTHY APPLEGATE</MerchantPrimaryContactName>' .
            '<MerchantPrimaryContactPhone>7858655392</MerchantPrimaryContactPhone>' .
            '<MerchantNumberOfLocations>1</MerchantNumberOfLocations><MerchantAddress><Street>KINGSTON DR</Street>' .
            '<City>LAWRENCE</City><Zip>660491614</Zip><UsState>KS</UsState></MerchantAddress>' .
            '<OwnerOfficers><OwnerOfficer><EmailAddress>landlord1@example.com</EmailAddress>' .
            '<FirstName>TIMOTHY</FirstName><LastName>APPLEGATE</LastName><HomePhone>7858655392</HomePhone>' .
            '<OwnerOfficerAddress><Street>KINGSTON DR</Street><City>LAWRENCE</City><Zip>660491614</Zip>' .
            '<UsState>KS</UsState></OwnerOfficerAddress></OwnerOfficer></OwnerOfficers><MerchantAccount>' .
            '<MerchantAccount><AccountNumber></AccountNumber><TransitRouterAbaNumber></TransitRouterAbaNumber>' .
            '<AccountType></AccountType></MerchantAccount></MerchantAccount>' .
            '</InboundApplicationConfiguration></InboundApplicationConfigurations></InboundConfiguration>' . "\n";

        $this->assertEquals($expected, $saml->getPortalApplication()->saveXml());
    }

    /**
     * @test
     */
    public function shouldUseBase64ToEncodeAssertionResponse()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $dom = new DOMDocument();
        $dom->appendChild($dom->createElement('TestElement', 'Some test data inside'));

        $this->assertEquals(
            'PFRlc3RFbGVtZW50PlNvbWUgdGVzdCBkYXRhIGluc2lkZTwvVGVzdEVsZW1lbnQ+',
            $saml->encodeAssertionResponse($dom)
        );
    }

    /**
     * @test
     */
    public function shouldCreateValidPortalApplicationData()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());

        $portalApplication = $saml->getPortalApplication();

        $this->assertTrue(
            $portalApplication->schemaValidate(__DIR__.'/../Fixtures/InboundApplicationConfiguration.xsd')
        );
    }

    /**
     * @test
     */
    public function shouldAddSuccessUrlAttributeIfItWasPassedAsAConstructorParameter()
    {
        $successUrl = 'http://rt.com/success';

        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount(), $successUrl);
        $assertionResponse = $saml->getAssertionResponse();
        $samlAttributes = $assertionResponse->getElementsByTagName('Attribute');

        $this->assertEquals(3, $samlAttributes->length);
        $this->assertEquals('successURL', $samlAttributes->item(2)->attributes->item(0)->nodeValue);
        $this->assertEquals($successUrl, $samlAttributes->item(2)->childNodes->item(0)->nodeValue);
    }

    /**
     * @test
     */
    public function shouldAddErrorUrlAttributeIfItWasPassedAsAConstructorParameter()
    {
        $successUrl = 'http://rt.com/success';
        $errorUrl = 'http://rt.com/error';

        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount(), $successUrl, $errorUrl);
        $assertionResponse = $saml->getAssertionResponse();
        $samlAttributes = $assertionResponse->getElementsByTagName('Attribute');

        $this->assertEquals(4, $samlAttributes->length);
        $this->assertEquals('successURL', $samlAttributes->item(2)->attributes->item(0)->nodeValue);
        $this->assertEquals('errorURL', $samlAttributes->item(3)->attributes->item(0)->nodeValue);
        $this->assertEquals($errorUrl, $samlAttributes->item(3)->childNodes->item(0)->nodeValue);
    }

    /**
     * @test
     */
    public function shouldNotAddErrorAndSuccessUrlAttributesIfTheyWereNotPassedAsConstructorParameters()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());
        $assertionResponse = $saml->getAssertionResponse();
        $samlAttributes = $assertionResponse->getElementsByTagName('Attribute');

        $this->assertEquals(2, $samlAttributes->length);
        $this->assertEquals('PortalApplicationSaml', $samlAttributes->item(0)->attributes->item(0)->nodeValue);
        $this->assertEquals('Email', $samlAttributes->item(1)->attributes->item(0)->nodeValue);
    }

    /**
     * @test
     */
    public function shouldAddEncodedPortalApplicationAsPortalApplicationSamlNode()
    {
        $saml = new SAMLEnvelope($this->getUser(), $this->getMerchantAccount());
        $assertionResponse = $saml->getAssertionResponse();
        $portalApplicationSaml = $assertionResponse->getElementsByTagName('Attribute')->item(0)->childNodes->item(0);

        $this->assertEquals($saml->encodePortalApplication(), $portalApplicationSaml->nodeValue);
    }

    protected function getMerchantAccount()
    {
        return new MerchantAccountModel('', '', '');
    }

    protected function getUser()
    {
        return self::$user;
    }
}
