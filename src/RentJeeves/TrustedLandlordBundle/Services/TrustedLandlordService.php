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
        $newCheckMailingAddress->setAddress1($address->getAddress1());

        $address2 = $address->getUnitDesignator() . $address->getUnitName();

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
        // here we do request for JIRA
        $this->statusManager->updateStatus($newTrustedLandlord, TrustedLandlordStatus::NEWONE);
        $this->em->persist($newTrustedLandlord);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatus(TrustedLandlord $trustedLandlord, $status)
    {
        $this->statusManager->updateStatus($trustedLandlord, $status);
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
            sprintf('%s %s', $trustedLandlordDTO->getAddress1(), $trustedLandlordDTO->getAddress2()),
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
