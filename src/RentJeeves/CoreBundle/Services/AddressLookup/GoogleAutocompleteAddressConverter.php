<?php
namespace RentJeeves\CoreBundle\Services\AddressLookup;

use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ValidatorInterface;

class GoogleAutocompleteAddressConverter
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @param string $jsonData
     *
     * @return Address
     */
    public function convert($jsonData)
    {
        $data = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->debug($message =
                sprintf(
                    '[GoogleAutocompleteAddressConverter] Invalid JSON : %s',
                    $jsonData
                )
            );
            throw new \InvalidArgumentException($message);
        }

        $newAddress = new Address();
        if (true === isset($data['address'])) {
            foreach ($data['address'] as $details) {
                if (isset($details['types'])) {
                    if (in_array('postal_code', $details['types'])) {
                        $newAddress->setZip($details['long_name']);
                    }
                    if (in_array('country', $details['types'])) {
                        $newAddress->setCountry($details['short_name']);
                    }
                    if (in_array('administrative_area_level_1', $details['types'])) {
                        $newAddress->setState($details['short_name']);
                    }
                    if (in_array('locality', $details['types'])) {
                        $newAddress->setCity($details['long_name']);
                    }
                    if (in_array('sublocality', $details['types'])) {
                        $newAddress->setDistrict($details['long_name']);
                    }
                    if (in_array('route', $details['types'])) {
                        $newAddress->setStreet($details['long_name']);
                    }
                    if (in_array('street_number', $details['types'])) {
                        $newAddress->setNumber($details['long_name']);
                    }
                }
            }
            if ($newAddress->getCity() == null && $newAddress != null) {
                $newAddress->setCity($newAddress->getDistrict());
                $newAddress->setDistrict(null);
            }
        }

        if (true === isset($data['geometry']['location'])) {
            if (count($data['geometry']['location']) === 2) {
                $newAddress->setJb(reset($data['geometry']['location']));
                $newAddress->setKb(end($data['geometry']['location']));
            }
        }

        $errors = $this->validate($newAddress);
        if (false === empty($errors)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '[GoogleAutocompleteAddressConverter] Address after convert JSON (%s) is not valid : %s.',
                    $jsonData,
                    implode(', ', $errors)
                )
            );
        }

        return $newAddress;
    }

    /**
     * @param Address $address
     *
     * @throws \InvalidArgumentException When Address is not valid
     *
     * @return array
     */
    protected function validate(Address $address)
    {
        $errors = [];
        /** @var ConstraintViolation $error */
        $validatorErrors = $this->validator->validate($address, ['GoogleAutocompleteAddress']);
        if ($validatorErrors->count() > 0) {
            foreach ($validatorErrors as $error) {
                $errors[] = sprintf(
                    '%s : %s',
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }
        }

        return $errors;
    }
}
