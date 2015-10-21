<?php

namespace RentJeeves\CoreBundle\Services\AddressLookup;

use Geocoder\Geocoder;
use Geocoder\Result\Geocoded;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ValidatorInterface;

class GoogleGeocoderAddressLookupService implements AddressLookupInterface
{
    /**
     * @var GeoCoder
     */
    protected $geoCoder;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param GeoCoder $geoCoder
     * @param LoggerInterface $logger
     */
    public function __construct(Geocoder $geoCoder, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->geoCoder = $geoCoder;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function lookup($street, $city, $state, $zipCode)
    {
        $address = sprintf(
            '%s, %s, %s %s',
            $street,
            $city,
            $state,
            $zipCode
        );
        $this->logger->debug(sprintf('[GoogleGeocoderAddressLookupService] Searching address (%s)', $address));

        try {
            $result = $this->geoCoder->using('cache')->geocode($address);
        } catch (\Exception $e) {
            $this->logger->error(
                $message = sprintf(
                    '[GoogleGeocoderAddressLookupService] Could not reach Google GeoCode : %s',
                    $e->getMessage()
                )
            );
            throw new AddressLookupException($message);
        }

        if (empty($result) || (!$result instanceof Geocoded)) {
            $this->logger->debug($message = '[GoogleGeocoderAddressLookupService] Google returned empty response');
            throw new AddressLookupException($message);
        }

        $address = $this->mapResponseToAddress($result);
        $errors = $this->validate($address);
        if (false === empty($errors)) {
            $this->logger->debug(
                $message = sprintf(
                    '[GoogleGeocoderAddressLookupService] Google returned invalid address : %s',
                    implode(', ', $errors)
                )
            );
            throw new AddressLookupException($message);
        }

        return $address;
    }

    /**
     * @param Geocoded $geocoded
     *
     * @return Address
     */
    protected function mapResponseToAddress(Geocoded $geocoded)
    {
        $address = new Address();
        $address->setJb($geocoded->getLatitude());
        $address->setKb($geocoded->getLongitude());
        $address->setNumber($geocoded->getStreetNumber());
        $address->setStreet($geocoded->getStreetName());
        $address->setZip($geocoded->getZipcode());
        $address->setCity($geocoded->getCity());
        $address->setDistrict($geocoded->getCityDistrict());
        $address->setCountry($geocoded->getCountryCode());
        $address->setState($geocoded->getRegionCode());
        if ($address->getCity() === null && $address->getDistrict() !== null) {
            $address->setCity($address->getDistrict());
        }

        return $address;
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
        $validatorErrors = $this->validator->validate($address, ['GoogleAddress']);
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
