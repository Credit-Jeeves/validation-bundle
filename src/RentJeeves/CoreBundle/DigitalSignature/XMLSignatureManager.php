<?php

namespace RentJeeves\CoreBundle\DigitalSignature;

use JMS\DiExtraBundle\Annotation as DI;
use XMLSecurityDSig as XMLDigitalSignature;

/**
 * @DI\Service("signature.manager")
 */
class XMLSignatureManager
{
    protected $privateKeyPath;
    protected $x509CertPath;
    /**
     * @DI\InjectParams({
     *     "privateKeyPath" = @DI\Inject("%digital_signature.private_key%"),
     *     "x509CertPath" = @DI\Inject("%digital_signature.certificate%")
     * })
     */
    public function __construct($privateKeyPath, $x509CertPath)
    {
        $this->privateKeyPath = $privateKeyPath;
        $this->x509CertPath = $x509CertPath;
    }

    public function sign(\DOMDocument $dom)
    {
        $digitalSignature = new XMLDigitalSignature('');
        $digitalSignature->setCanonicalMethod(XMLDigitalSignature::EXC_C14N);
        $digitalSignature->addReference(
            $dom->documentElement,
            XMLDigitalSignature::SHA1,
            array('http://www.w3.org/2000/09/xmldsig#enveloped-signature','http://www.w3.org/2001/10/xml-exc-c14n#'),
            array('id_name' => 'ID', 'overwrite' => false)
        );

        $privateKey = new \XMLSecurityKey(
            \XMLSecurityKey::RSA_SHA1,
            array('type'=>'private')
        );

        $privateKey->loadKey($this->privateKeyPath, true);

        $digitalSignature->sign($privateKey);

        $digitalSignature->add509Cert(file_get_contents($this->x509CertPath));

        $digitalSignature->appendSignature($dom->documentElement);

        return $dom;
    }
}
