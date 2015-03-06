<?php

namespace RentJeeves\CheckoutBundle\PaymentProcessor\Heartland\Serializer\Encoder;

use RentJeeves\ComponentBundle\FileReader\CsvFileReader;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class CsvFileDecoder implements DecoderInterface
{
    const FORMAT = 'hps_csv_file';

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = array())
    {
        if (is_file($data)) {
            $reader = new CsvFileReader();
            $decodedData = $reader->read($data);
            return $decodedData;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return self::FORMAT === $format;
    }
}
