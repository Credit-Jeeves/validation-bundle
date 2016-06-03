<?php

namespace RentJeeves\CoreBundle\Services\AddressLookup;

use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Helpers\CountryNameStandardizer;
use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\DataBundle\Enum\CountryCode;
use RentTrack\SmartyStreetsBundle\Exception\SmartyStreetsException;
use RentTrack\SmartyStreetsBundle\Model\International\InternationalAddress;
use RentTrack\SmartyStreetsBundle\Model\US\USAddress;
use RentTrack\SmartyStreetsBundle\SmartyStreetsClient;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Service`s name "address_lookup_service.smarty_streets"
 */
class SmartyStreetsAddressLookupService implements AddressLookupInterface
{
    /**
     * @var SmartyStreetsClient
     */
    protected $smartyStreetsClient;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param SmartyStreetsClient $ssClient
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     */
    public function __construct(SmartyStreetsClient $ssClient, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->smartyStreetsClient = $ssClient;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function lookup($street, $city, $state, $zipCode, $country = AddressLookupInterface::COUNTRY_US)
    {
        $this->logger->debug(
            sprintf(
                '[SmartyStreetsAddressLookupService] Searching address (%s %s %s %s %s)',
                $street,
                $city,
                $state,
                $zipCode,
                $country
            )
        );

        $country = CountryNameStandardizer::standardize($country);
        if (false === CountryCode::isValid($country)) {
            throw new AddressLookupException(
                sprintf('[SmartyStreetsAddressLookupService] Country "%s" not supported.', $country)
            );
        }

        if ($country === AddressLookupInterface::COUNTRY_US) {
            $address = $this->lookupUSAddress($street, $city, $state, $zipCode);
        } else {
            $address = $this->lookupInternationalAddress($street, $city, $state, $zipCode, $country);
        }

        $this->validate($address);

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function lookupFreeform($address, $country = self::COUNTRY_US)
    {
        $this->logger->debug(
            sprintf(
                '[SmartyStreetsAddressLookupService] Searching freeForm address (%s %s)',
                $address,
                $country
            )
        );

        $country = CountryNameStandardizer::standardize($country);
        if (false === CountryCode::isValid($country)) {
            throw new AddressLookupException(
                sprintf('[SmartyStreetsAddressLookupService] Country "%s" not supported.', $country)
            );
        }

        if ($country === AddressLookupInterface::COUNTRY_US) {
            // First, we have to remove ', United States'
            // from the freeform address in case user chose Google Autocomplete
            $address = str_replace(', United States', '', $address);
            $address = $this->lookupUSAddress($address, '', '', '');
        } else {
            $address = $this->lookupInternationalAddress($address, '', '', '', $country);
        }

        $this->validate($address);

        return $address;
    }

    /**
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zipCode
     *
     * @throws AddressLookupException
     *
     * @return Address
     */
    protected function lookupUSAddress($street, $city, $state, $zipCode)
    {
        try {
            $result = $this->smartyStreetsClient->getUSAddress($street, $city, $state, $zipCode);
        } catch (SmartyStreetsException $e) {
            $this->logger->debug(
                $message = sprintf(
                    '[SmartyStreetsAddressLookupService] Address not found : %s',
                    $e->getMessage()
                )
            );
            throw new AddressLookupException($message);
        }

        return $this->mapUSResponseToAddress($result);
    }

    /**
     * @param string $street
     * @param string $city
     * @param string $state
     * @param string $zipCode
     * @param string $country
     *
     * @throws AddressLookupException
     *
     * @return Address
     */
    protected function lookupInternationalAddress($street, $city, $state, $zipCode, $country)
    {
        try {
            $result = $this->smartyStreetsClient->getInternationalAddress($street, $city, $state, $zipCode, $country);
        } catch (SmartyStreetsException $e) {
            $this->logger->debug(
                $message = sprintf(
                    '[SmartyStreetsAddressLookupService] Address not found : %s',
                    $e->getMessage()
                )
            );
            throw new AddressLookupException($message);
        }

        return $this->mapInternationalResponseToAddress($result);
    }

    /**
     * @param USAddress $ssAddress
     *
     * @return Address
     */
    protected function mapUSResponseToAddress(USAddress $ssAddress)
    {
        $addressMetadata = $ssAddress->getMetadata();
        $addressComponents = $ssAddress->getComponents();
        $address = new Address();
        $address->setLatitude($addressMetadata->getLatitude());
        $address->setLongitude($addressMetadata->getLongitude());
        $address->setNumber($addressComponents->getPrimaryNumber());
        $street = sprintf(
            '%s %s %s',
            $addressComponents->getStreetPredirection(),
            $addressComponents->getStreetName(),
            $addressComponents->getStreetSuffix()
        );
        $address->setStreet(trim($street));
        $address->setZip($addressComponents->getZipcode());
        $address->setCity($addressComponents->getCityName());
        $address->setCountry(self::COUNTRY_US);
        $address->setState($addressComponents->getStateAbbreviation());
        $address->setUnitName($addressComponents->getSecondaryNumber());
        $address->setUnitDesignator($addressComponents->getSecondaryDesignator());

        return $address;
    }

    /**
     * @param InternationalAddress $ssAddress
     *
     * @return Address
     */
    protected function mapInternationalResponseToAddress(InternationalAddress $ssAddress)
    {
        $addressMetadata = $ssAddress->getMetadata();
        $addressComponents = $ssAddress->getComponents();
        $address = new Address();
        $address->setLatitude($addressMetadata->getLatitude());
        $address->setLongitude($addressMetadata->getLongitude());
        $address->setNumber($addressComponents->getPremiseNumber());
        $address->setStreet($addressComponents->getThoroughfare());
        $address->setZip($addressComponents->getPostalCode());
        $address->setCity($addressComponents->getLocality());
        $address->setCountry(CountryNameStandardizer::standardize($addressComponents->getCountryISO()));
        $address->setState($addressComponents->getAdministrativeArea());
        $address->setUnitName($addressComponents->getSubBuildingNumber());

        return $address;
    }

    /**
     * @param Address $address
     *
     * @throws AddressLookupException if returned address is not valid
     */
    protected function validate(Address $address)
    {
        $errors = [];
        /** @var ConstraintViolation $error */
        $validatorErrors = $this->validator->validate($address, ['SmartyStreetsAddress']);
        if ($validatorErrors->count() > 0) {
            foreach ($validatorErrors as $error) {
                $errors[] = sprintf(
                    '%s : %s',
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }
        }

        if (false === empty($errors)) {
            $this->logger->debug(
                $message = sprintf(
                    '[SmartyStreetsAddressLookupService] SmartyStreets returned invalid address : %s',
                    implode(', ', $errors)
                )
            );
            throw new AddressLookupException($message);
        }
    }
}
