<?php

namespace RentJeeves\TrustedLandlordBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\AddressLookupInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\DataBundle\Entity\CheckMailingAddress;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;
use RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordServiceException;
use RentJeeves\TrustedLandlordBundle\Exception\TrustedLandlordStatusException;
use RentJeeves\TrustedLandlordBundle\Model\TrustedLandlordDTO;

/**
 * Service`s name "trusted_landlord_service"
 */
class TrustedLandlordService implements TrustedLandlordServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AddressLookupInterface
     */
    protected $lookupService;

    /**
     * @var TrustedLandlordStatusManager
     */
    protected $statusManager;

    /**
     * @param EntityManagerInterface       $em
     * @param LoggerInterface              $logger
     * @param AddressLookupInterface       $lookupService
     * @param TrustedLandlordStatusManager $statusManager
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        AddressLookupInterface $lookupService,
        TrustedLandlordStatusManager $statusManager
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->lookupService = $lookupService;
        $this->statusManager = $statusManager;
    }

    /**
     * {@inheritdoc}
     */
    public function lookup(TrustedLandlordDTO $trustedLandlordDTO)
    {
        $this->logger->debug('Try to find TrustedLandlord.');

        try {
            $address = $this->lookupAddressByTrustedLandlordDTO($trustedLandlordDTO);
        } catch (AddressLookupException $e) {
            $this->logger->warning('TrustedLandlord not found:' . $e->getMessage());

            return null;
        }
        /** @var CheckMailingAddress $mailingAddress */
        $mailingAddress = $this->getCheckMailingAddressRepository()->findOneBy(['index' => $address->getIndex()]);
        if (null === $mailingAddress) {
            $this->logger->debug(sprintf('TrustedLandlord not found by ssIndex = %s:', $address->getIndex()));

            return null;
        }

        return $mailingAddress->getTrustedLandlord();
    }

    /**
     * {@inheritdoc}
     */
    public function create(TrustedLandlordDTO $trustedLandlordDTO)
    {
        $this->logger->debug('Try to create new TrustedLandlord.');

        try {
            $address = $this->lookupAddressByTrustedLandlordDTO($trustedLandlordDTO);
        } catch (AddressLookupException $e) {
            $this->logger->warning($e->getMessage());
            throw new TrustedLandlordServiceException($e->getMessage());
        }

        if (null !== $this->getCheckMailingAddressRepository()->findOneBy(['index' => $address->getIndex()])) {
            $this->logger->warning(
                $message = sprintf(
                    'Cant create new TrustedLandlord: CheckMailingAddress with index "%s" already exists.',
                    $address->getIndex()
                )
            );
            throw new TrustedLandlordServiceException($message);
        }

        $newCheckMailingAddress = new CheckMailingAddress();
        $newCheckMailingAddress->setAddressee($trustedLandlordDTO->getAddressee());
        $newCheckMailingAddress->setState($address->getState());
        $newCheckMailingAddress->setCity($address->getCity());
        $newCheckMailingAddress->setExternalLocationId($trustedLandlordDTO->getLocationId());
        $newCheckMailingAddress->setAddress1($address->getAddress1());

        $address2 = $trustedLandlordDTO->getAddress2();

        $newCheckMailingAddress->setAddress2($address2 ?: null);
        $newCheckMailingAddress->setZip($address->getZip());
        $newCheckMailingAddress->setIndex($address->getIndex());

        $newTrustedLandlord = new TrustedLandlord();
        $newTrustedLandlord->setCheckMailingAddress($newCheckMailingAddress);
        $newTrustedLandlord->setFirstName($trustedLandlordDTO->getFirstName());
        $newTrustedLandlord->setLastName($trustedLandlordDTO->getLastName());
        $newTrustedLandlord->setCompanyName($trustedLandlordDTO->getCompanyName());
        $newTrustedLandlord->setType($trustedLandlordDTO->getType());
        $newTrustedLandlord->setPhone($trustedLandlordDTO->getPhone());
        $newTrustedLandlord->setStatus(TrustedLandlordStatus::INITIATED);
        $this->em->persist($newTrustedLandlord);
        $this->em->flush();
        $this->updateStatus($newTrustedLandlord, TrustedLandlordStatus::NEWONE);

        return $newTrustedLandlord;
    }

    /**
     * {@inheritdoc}
     */
    public function update(TrustedLandlord $trustedLandlord, $status, TrustedLandlordDTO $trustedLandlordDTO = null)
    {
        $this->logger->debug('Try to update TrustedLandlord#'.$trustedLandlord->getId());

        if (empty($trustedLandlordDTO)) {
            $this->updateStatus($trustedLandlord, $status);

            return;
        }

        try {
            $address = $this->lookupAddressByTrustedLandlordDTO($trustedLandlordDTO);
        } catch (AddressLookupException $e) {
            $this->logger->warning($e->getMessage());
            throw new TrustedLandlordServiceException($e->getMessage());
        }
        /** @var CheckMailingAddress $checkMailingAddressInDB */
        $checkMailingAddressInDB = $this->getCheckMailingAddressRepository()->findOneBy(
            ['index' => $address->getIndex()]
        );

        if (!empty($checkMailingAddressInDB) &&
            $checkMailingAddressInDB->getId() !== $trustedLandlord->getCheckMailingAddress()->getId()
        ) {
            $this->logger->warning(
                $message = sprintf(
                    'Cant update TrustedLandlord#%s: CheckMailingAddress with index "%s" already exists.',
                    $trustedLandlord->getId(),
                    $address->getIndex()
                )
            );

            throw new TrustedLandlordServiceException($message);
        }

        $checkMailingAddress = $trustedLandlord->getCheckMailingAddress();
        $checkMailingAddress->setAddressee($trustedLandlordDTO->getAddressee());
        $checkMailingAddress->setState($address->getState());
        $checkMailingAddress->setCity($address->getCity());
        $checkMailingAddress->setAddress1($address->getAddress1());

        $address2 = $trustedLandlordDTO->getAddress2();

        $checkMailingAddress->setAddress2($address2 ?: null);
        $checkMailingAddress->setZip($address->getZip());
        $checkMailingAddress->setIndex($address->getIndex());

        $trustedLandlord->setFirstName($trustedLandlordDTO->getFirstName());
        $trustedLandlord->setLastName($trustedLandlordDTO->getLastName());
        $trustedLandlord->setCompanyName($trustedLandlordDTO->getCompanyName());
        $trustedLandlord->setType($trustedLandlordDTO->getType());
        $trustedLandlord->setPhone($trustedLandlordDTO->getPhone());
        $this->updateStatus($trustedLandlord, $status);
    }

    /**
     * @param TrustedLandlord $trustedLandlord
     * @param string $status
     * @throws TrustedLandlordServiceException
     */
    protected function updateStatus(TrustedLandlord $trustedLandlord, $status)
    {
        try {
            $this->statusManager->updateStatus($trustedLandlord, $status);
        } catch (TrustedLandlordStatusException $e) {
            throw new TrustedLandlordServiceException($e->getMessage());
        }
    }

    /**
     * @param TrustedLandlordDTO $trustedLandlordDTO
     *
     * @throws AddressLookupException if address is not valid
     *
     * @return Address
     */
    protected function lookupAddressByTrustedLandlordDTO(TrustedLandlordDTO $trustedLandlordDTO)
    {
        return $this->lookupService->lookup(
            $trustedLandlordDTO->getAddress1(),
            $trustedLandlordDTO->getCity(),
            $trustedLandlordDTO->getState(),
            $trustedLandlordDTO->getZip()
        );
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getCheckMailingAddressRepository()
    {
        return $this->em->getRepository('RjDataBundle:CheckMailingAddress');
    }
}
