<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Aci\Encoder;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use RentJeeves\CheckoutBundle\PaymentProcessor\Exception\AciDecoderException;

/**
 * @DI\Service("payment_processor.aci.pgp_decoder")
 */
class AciPgpDecoder implements FileDecoderInterface
{
    /**
     * @var string
     */
    private $pgpKey;

    /**
     * @param string $pgpKeyPath
     *
     * @InjectParams({"pgpKeyPath" = @Inject("%aci.key_path%")})
     */
    public function __construct($pgpKeyPath)
    {
        if (false === is_readable($pgpKeyPath)) {
            throw new \InvalidArgumentException('PGP key is not found or can not be read');
        }

        $this->pgpKey = file_get_contents($pgpKeyPath);
    }

    /**
     * Decode file to string
     *
     * @param string $inputFileName
     *
     * @return string Decoded data
     *
     * @throws AciDecoderException
     */
    public function decode($inputFileName)
    {
        if (false === is_readable($inputFileName)) {
            throw new AciDecoderException(sprintf('File "%s" does not exist or can not be read', $inputFileName));
        }

        $fileData = file_get_contents($inputFileName);

        try {
            $gpg = new \gnupg();
            $gpg->seterrormode(\gnupg::ERROR_EXCEPTION);
            $gpg->import($this->pgpKey);

            return $gpg->decrypt($fileData);
        } catch (\Exception $e) {
            throw new AciDecoderException(sprintf('Gnupg`s error : %s', $e->getMessage()));
        }
    }
}
