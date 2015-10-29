<?php

namespace RentJeeves\CoreBundle\Services\AddressLookup;

use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentTrack\SmartyStreetsBundle\Exception\SmartyStreetsException;
use RentTrack\SmartyStreetsBundle\Model\SmartyStreetsAddress;
use RentTrack\SmartyStreetsBundle\SmartyStreetsClient;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ValidatorInterface;

class SmartyStreetsAddressLookupService implements AddressLookupInterface
{
    const DEFAULT_COUNTRY = 'US';

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
    public function lookup($street, $city, $state, $zipCode)
    {
        $this->logger->debug(
            sprintf(
                '[SmartyStreetsAddressLookupService] Searching address (%s %s %s %s)',
                $street,
                $city,
                $state,
                $zipCode
            )
        );
        try {
            $result = $this->smartyStreetsClient->getAddress($street, $city, $state, $zipCode);
        } catch (SmartyStreetsException $e) {
            $this->logger->debug(
                $message = sprintf(
                    '[SmartyStreetsAddressLookupService] Address not found : %s',
                    $e->getMessage()
                )
            );
            throw new AddressLookupException($message);
        }

        $address = $this->mapResponseToAddress($result);
        $this->validate($address);

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function lookupFreeform($address)
    {
        $this->logger->debug(sprintf('[SmartyStreetsAddressLookupService] Searching freeForm address (%s)', $address));
        try {
            $result = $this->smartyStreetsClient->getAddress($address, '', '', '');
        } catch (SmartyStreetsException $e) {
            $this->logger->debug(
                $message = sprintf('[SmartyStreetsAddressLookupService] Address not found : %s', $e->getMessage())
            );
            throw new AddressLookupException($message);
        }

        $address = $this->mapResponseToAddress($result);
        $this->validate($address);

        return $address;
    }

    /**
     * @param SmartyStreetsAddress $ssAddress
     *
     * @return Address
     */
    protected function mapResponseToAddress(SmartyStreetsAddress $ssAddress)
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
        $address->setCountry(self::DEFAULT_COUNTRY);
        $address->setState($addressComponents->getStateAbbreviation());

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
