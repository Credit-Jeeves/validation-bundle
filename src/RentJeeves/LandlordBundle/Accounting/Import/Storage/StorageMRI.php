<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use \RentJeeves\CoreBundle\Session\Landlord as SessionLandlord;

/**
 * @Service("accounting.import.storage.mri")
 */
class StorageMRI extends ExternalApiStorage
{
    const IS_CURRENT = 'y';

    /**
     * @Inject("doctrine.orm.entity_manager", required = false)
     *
     * @var EntityManager
     */
    public $em;

    /**
     * @Inject("core.session.landlord", required = false)
     *
     * @var SessionLandlord
     */
    public $sessionLandlordManager;

    /**
     * @return bool
     */
    public function isMultipleProperty()
    {
        return true;
    }

    /**
     * @return Landlord
     */
    protected function getLandlord()
    {
        return $this->sessionLandlordManager->getUser();
    }

    /**
     * @return array|bool
     */
    protected function getMappingFromDB()
    {
        $importApiMapping = $this->em->getRepository('RjDataBundle:ImportApiMapping')->findOneBy(
            [
                'externalPropertyId' => $this->getImportExternalPropertyId(),
                'holding' => $this->getLandlord()->getHolding()
            ]
        );

        if (empty($importApiMapping)) {
            return false;
        }

        return $importApiMapping->getMappingData();
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeParameters()
    {
        $this->setFieldDelimiter(self::FIELD_DELIMITER);
        $this->setTextDelimiter(self::TEXT_DELIMITER);
        $this->setDateFormat(self::DATE_FORMAT);

        if (!$mapping = $this->getMappingFromDB()) {
            $mapping = [
                1 => Mapping::KEY_RESIDENT_ID,
                2 => Mapping::KEY_UNIT,
                3 => Mapping::KEY_MOVE_IN,
                4 => Mapping::KEY_LEASE_END,
                5 => Mapping::KEY_RENT,
                6 => Mapping::FIRST_NAME_TENANT,
                7 => Mapping::LAST_NAME_TENANT,
                8 => Mapping::KEY_EMAIL,
                9 => Mapping::KEY_MOVE_OUT,
                10 => Mapping::KEY_BALANCE,
                11 => Mapping::KEY_MONTH_TO_MONTH,
                12 => Mapping::KEY_PAYMENT_ACCEPTED,
                13 => Mapping::KEY_EXTERNAL_LEASE_ID,
                14 => Mapping::KEY_UNIT_ID,
                15 => Mapping::KEY_CITY,
                16 => Mapping::KEY_STREET,
                17 => Mapping::KEY_ZIP,
                18 => Mapping::KEY_STATE,
                19 => Mapping::KEY_EXTERNAL_PROPERTY_ID
            ];
        }

        $this->writeCsvToFile($mapping);
        $this->setMapping($mapping);
    }

    /**
     * @param  array $customers
     * @return bool
     */
    public function saveToFile(array $customers)
    {
        if (empty($customers)) {
            return false;
        }

        // API execution can be long, so restart the execution
        // timeout counter from zero and give us another 2 min (120 sec)
        set_time_limit(120);

        /** @var $customer Value  */
        foreach ($customers as $customer) {
            $filePath = $this->getFilePath(true);
            if (is_null($filePath)) {
                $this->initializeParameters();
            }

            if (strtolower($customer->getIsCurrent()) !== strtolower(self::IS_CURRENT)) {
                continue;
            }
            $leaseStart = $customer->getLeaseStart();
            if ($leaseStart) {
                $startAt = $this->getDateString($leaseStart);
            } else {
                $startAt = $this->getDateString($customer->getOccupyDateFormatted());
            }

            $address = $customer->getAddress() ? $customer->getAddress() : $customer->getBuildingAddress();

            $finishAt = $this->getDateString($customer->getLeaseEnd());
            $moveOut = $this->getDateString($customer->getLeaseMoveOut());
            $monthToMonth = $customer->getLeaseMonthToMonth();
            $isCurrent = $customer->getIsCurrent();
            $monthToMonth = strtolower($isCurrent) === 'y' ? $isCurrent : $monthToMonth;

            $externalUnitId = $customer->getExternalUnitId();
            $unitName = $customer->getUnitId();

            $data = [
                $customer->getResidentId(),
                $unitName,
                $startAt,
                $finishAt,
                $customer->getLeaseMonthlyRentAmount(),
                $customer->getFirstName(),
                $customer->getLastName(),
                $customer->getEmail(),
                $moveOut,
                $customer->getLeaseBalance(),
                $monthToMonth,
                $customer->getPaymentAccepted(),
                $customer->getLeaseId(),
                $externalUnitId,
                $customer->getCity(),
                $address,
                $customer->getZipCode(),
                $customer->getState(),
                $this->getImportExternalPropertyId(),
                $customer->getBuildingAddress()
            ];

            $this->writeCsvToFile($data);
        }

        return true;
    }
}
