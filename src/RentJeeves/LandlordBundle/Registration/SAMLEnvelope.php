<?php

namespace RentJeeves\LandlordBundle\Registration;

use CreditJeeves\DataBundle\Entity\User;
use RentJeeves\LandlordBundle\Registration\MerchantAccountModel as MerchantAccount;
use \XMLSecurityDSig as XMLDigitalSignature;
use \DOMDocument;

class SAMLEnvelope
{
    protected $user;
    protected $merchantAccount;

    protected $portalApplication;
    protected $assertionResponse;

    protected $successUrl;
    protected $errorUrl;

    const DESTINATION = 'https://partners.heartlandpaymentsystems.com/renttrack';
    const ONLINE_BOARDING = 'https://onlineboarding.heartlandpaymentsystems.com/renttrack';
    const ISSUER = 'https://www.renttrack.com';
    const NAME_QUALIFIER = 'renttrack.com';
    const AUDIENCE = 'partners.heartlandpaymentsystems.com/renttrack';
    const SUBJECT_LOCALITY_ADDRESS = 'fe80::70fd:2bf7:fa4f:ba6f%10';
    const RESPONSE_ID_PREFIX = 'arp';

    public function __construct(User $user, MerchantAccount $merchantAccount, $successUrl = null, $errorUrl = null)
    {
        $this->user = $user;
        $this->merchantAccount = $merchantAccount;
        $this->successUrl = $successUrl;
        $this->errorUrl = $errorUrl;

        $this->createPortalApplicationData();
        $this->createAssertionResponse();
    }

    /**
     * @return \DOMElement
     */
    public function getAssertionResponse()
    {
        return $this->assertionResponse;
    }

    /**
     * @return \DOMDocument
     */
    public function getPortalApplication()
    {
        return $this->portalApplication;
    }

    /**
     * @return string
     */
    public function encodePortalApplication()
    {
        return base64_encode($this->portalApplication->saveXML());
    }

    /**
     * @param \DOMDocument $signedDocument
     * @return string
     */
    public function encodeAssertionResponse(DOMDocument $signedDocument)
    {
        return base64_encode($signedDocument->saveXML($signedDocument->documentElement));
    }

    protected function createPortalApplicationData()
    {
        $doc = new DOMDocument('1.0', 'utf-8');

        $root = $doc->createElement('InboundConfiguration');
        $parentApplicationNode = $doc->createElement('InboundApplicationConfigurations');

        $application = $doc->createElement('InboundApplicationConfiguration');
        $application->appendChild($doc->createElement('MerchantDbaName', $this->user->getFullName()));
        $application->appendChild($doc->createElement('MerchantEmail', $this->user->getEmail()));
        if ($phone = $this->user->getPhone()) {
            $application->appendChild($doc->createElement('MerchantPhone', $phone));
        }
        $application->appendChild($doc->createElement('CorporateName', $this->user->getFullName()));
        $application->appendChild($doc->createElement('CorporatePhone', $this->user->getPhone()));
        $application->appendChild($doc->createElement('MerchantPrimaryContactName', $this->user->getFullName()));
        $application->appendChild($doc->createElement('MerchantPrimaryContactPhone', $this->user->getPhone()));
        $application->appendChild($doc->createElement('MerchantNumberOfLocations', 1));

        $userAddress = $this->user->getAddresses()->last();
        $street = $doc->createElement('Street', $userAddress? $userAddress->getStreet() : '');
        $city = $doc->createElement('City', $userAddress? $userAddress->getCity() : '');
        $zip = $doc->createElement('Zip', $userAddress? $userAddress->getZip() : '');
        $state = $doc->createElement('UsState', $userAddress? $userAddress->getArea() : '');

        $merchantAddress = $doc->createElement('MerchantAddress');
        $merchantAddress->appendChild($street);
        $merchantAddress->appendChild($city);
        $merchantAddress->appendChild($zip);
        $merchantAddress->appendChild($state);
        $application->appendChild($merchantAddress);

        $ownerOfficer = $doc->createElement('OwnerOfficer');
        $ownerOfficer->appendChild($doc->createElement('EmailAddress', $this->user->getEmail()));
        $ownerOfficer->appendChild($doc->createElement('FirstName', $this->user->getFirstName()));
        $ownerOfficer->appendChild($doc->createElement('LastName', $this->user->getLastName()));
        $ownerOfficer->appendChild($doc->createElement('HomePhone', $this->user->getPhone()));

        $ownerOfficerAddress = $doc->createElement('OwnerOfficerAddress');
        $ownerOfficerAddress->appendChild(clone $street);
        $ownerOfficerAddress->appendChild(clone $city);
        $ownerOfficerAddress->appendChild(clone $zip);
        $ownerOfficerAddress->appendChild(clone $state);
        $ownerOfficer->appendChild($ownerOfficerAddress);

        $ownerOfficers = $doc->createElement('OwnerOfficers');
        $ownerOfficers->appendChild($ownerOfficer);
        $application->appendChild($ownerOfficers);

        $merchantAccount = $doc->createElement('MerchantAccount');
        $merchantAccount->appendChild($doc->createElement('AccountNumber', $this->merchantAccount->getAccountNumber()));
        $merchantAccount->appendChild(
            $doc->createElement('TransitRouterAbaNumber', $this->merchantAccount->getRoutingNumber())
        );
        $merchantAccount->appendChild($doc->createElement('AccountType', $this->merchantAccount->getAccountType()));

        $parentMerchantAccount = $doc->createElement('MerchantAccount');
        $parentMerchantAccount->appendChild($merchantAccount);
        $application->appendChild($parentMerchantAccount);

        $parentApplicationNode->appendChild($application);
        $root->appendChild($parentApplicationNode);
        $doc->appendChild($root);

        $this->portalApplication = $doc;
    }

    protected function createAssertionResponse()
    {
        $doc = new DOMDocument('1.0', 'utf-8');
        $root = $doc->createElementNS('urn:oasis:names:tc:SAML:2.0:protocol', 'Response');
        $root->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $root->setAttribute('ID', XMLDigitalSignature::generate_GUID(self::RESPONSE_ID_PREFIX));
        $root->setAttribute('Version', '2.0');
        $root->setAttribute('IssueInstant', gmdate("Y-m-d\TH:i:s\Z"));
        $root->setAttribute('Destination', self::DESTINATION);

        $issuer = $doc->createElement('Issuer', self::ISSUER);
        $issuer->setAttribute('xmlns', 'urn:oasis:names:tc:SAML:2.0:assertion');
        $root->appendChild($issuer);

        $status = $doc->createElement('Status');
        $statusCode = $doc->createElement('StatusCode');
        $statusCode->setAttribute('Value', 'urn:oasis:names:tc:SAML:2.0:status:Success');
        $status->appendChild($statusCode);
        $root->appendChild($status);

        $assertion = $doc->createElement('Assertion');
        $assertion->setAttribute('xmlns', 'urn:oasis:names:tc:SAML:2.0:assertion');
        $assertion->setAttribute('Version', '2.0');
        $assertion->setAttribute('IssueInstant', gmdate("Y-m-d\TH:i:s\Z"));
        $assertion->appendChild($doc->createElement('Issuer', self::ISSUER));

        $nameId = $doc->createElement('NameID', $this->user->getEmail());
        $nameId->setAttribute('NameQualifier', self::NAME_QUALIFIER);

        $subjectConfirmation = $doc->createElement('SubjectConfirmation');
        $subjectConfirmation->setAttribute('Method', 'urn:oasis:names:tc:SAML:2.0:cm:bearer');
        $subjectConfirmation->appendChild($doc->createElement('SubjectConfirmationData'));

        $subject = $doc->createElement('Subject');
        $subject->appendChild($nameId);
        $subject->appendChild($subjectConfirmation);

        $assertion->appendChild($subject);

        $conditions = $doc->createElement('Conditions');
        $conditions->setAttribute('NotBefore', gmdate("Y-m-d\TH:i:s\Z"));
        $conditions->setAttribute('NotOnOrAfter', gmdate("Y-m-d\TH:i:s\Z"));

        $audienceRestriction = $doc->createElement('AudienceRestriction');
        $audienceRestriction->appendChild($doc->createElement('Audience', self::AUDIENCE));
        $conditions->appendChild($audienceRestriction);

        $assertion->appendChild($conditions);

        $authnStatement = $doc->createElement('AuthnStatement');
        $authnStatement->setAttribute('AuthnInstant', gmdate("Y-m-d\TH:i:s\Z"));

        $subjectLocality = $doc->createElement('SubjectLocality');
        $subjectLocality->setAttribute('Address', self::SUBJECT_LOCALITY_ADDRESS);

        $authnStatement->appendChild($subjectLocality);

        $authnContext = $doc->createElement('AuthnContext');
        $authnContext->appendChild($doc->createElement('AuthnContextClassRef', 'AuthnContextClassRef'));

        $authnStatement->appendChild($authnContext);

        $assertion->appendChild($authnStatement);

        $attributeStatement = $doc->createElement('AttributeStatement');
        $attributeApplicationNode = $doc->createElement('Attribute');
        $attributeApplicationNode->setAttribute('Name', 'PortalApplicationSaml');
        $attributeApplicationNode->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:basic');

        $attributeValueNode = $doc->createElement('AttributeValue', $this->encodePortalApplication());
        $attributeValueNode->setAttribute('xsi:type', 'xsd:string');

        $attributeApplicationNode->appendChild($attributeValueNode);
        $attributeStatement->appendChild($attributeApplicationNode);

        $attributeEmailNode = $doc->createElement('Attribute');
        $attributeEmailNode->setAttribute('Name', 'Email');
        $attributeEmailNode->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:basic');

        $attributeEmailValueNode = $doc->createElement('AttributeValue', $this->user->getEmail());
        $attributeEmailValueNode->setAttribute('xsi:type', 'xsd:string');

        $attributeEmailNode->appendChild($attributeEmailValueNode);
        $attributeStatement->appendChild($attributeEmailNode);

        if ($this->successUrl) {
            $successUrl = $doc->createElement('Attribute');
            $successUrl->setAttribute('Name', 'successURL');
            $successUrl->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:basic');

            $successUrlValue = $doc->createElement('AttributeValue', $this->successUrl);
            $successUrlValue->setAttribute('xsi:type', 'xsd:string');

            $successUrl->appendChild($successUrlValue);
            $attributeStatement->appendChild($successUrl);
        }

        if ($this->errorUrl) {
            $errorUrl = $doc->createElement('Attribute');
            $errorUrl->setAttribute('Name', 'errorURL');
            $errorUrl->setAttribute('NameFormat', 'urn:oasis:names:tc:SAML:2.0:attrname-format:basic');

            $errorUrlValue = $doc->createElement('AttributeValue', $this->errorUrl);
            $errorUrlValue->setAttribute('xsi:type', 'xsd:string');

            $errorUrl->appendChild($errorUrlValue);
            $attributeStatement->appendChild($errorUrl);
        }

        $assertion->appendChild($attributeStatement);
        $root->appendChild($assertion);
        $doc->appendChild($root);

        $this->assertionResponse = $doc;
        $this->cleanAssertion();
    }

    /**
     * The only found way to avoid broken namespaces is to save and load generated xml.
     * Otherwise new unnecessary namespaces are appeared and HPS refuses the authentication.
     */
    private function cleanAssertion()
    {
        $filename = "/tmp/{$this->user->getId()}.xml";
        file_put_contents($filename, $this->assertionResponse->saveXML($this->assertionResponse->documentElement));
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->load($filename);
        unlink($filename);

        $this->assertionResponse = $doc;
    }
}
