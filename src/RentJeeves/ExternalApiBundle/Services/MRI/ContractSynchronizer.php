<?php

namespace RentJeeves\ExternalApiBundle\Services\MRI;

use CreditJeeves\DataBundle\Entity\Holding;
use Psr\Log\LogLevel;
use RentJeeves\CoreBundle\Helpers\DateChecker;
use RentJeeves\DataBundle\Entity\ContractWaiting;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PropertyMapping;
use RentJeeves\ExternalApiBundle\Model\MRI\Charge;
use RentJeeves\ExternalApiBundle\Model\MRI\Resident;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;
use RentJeeves\ExternalApiBundle\Services\AbstractContractSynchronizer;

/**
 * DI\Service("mri.contract_sync")
 */
class ContractSynchronizer extends AbstractContractSynchronizer
{
    const LOGGER_PREFIX = '[MRI ContractSynchronizer]';

    /**
     * {@inheritdoc}
     */
    protected function setExternalSettings(Holding $holding)
    {
        $this->residentDataManager->setSettings($holding->getMriSettings());
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingBalance()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingBalanceMRI();
    }

    /**
     * @param Value $resident
     * @param PropertyMapping $propertyMapping
     * @throws \Exception
     */
    protected function updateContractBalanceForResidentTransaction(
        $resident,
        PropertyMapping $propertyMapping
    ) {
        $residentId = $resident->getResidentId();
        $externalUnitId = $resident->getExternalUnitId();
        if (null === $contract = $this->getContract($propertyMapping, $residentId, $externalUnitId)) {
            return;
        }

        $this->logMessage(
            sprintf(
                '[SyncBalance]Processing %s #%d.',
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId()
            )
        );

        $this->doUpdateBalanceAndPaymentAccepted($resident, $contract);
    }

    /**
     * @param PropertyMapping $propertyMapping
     * @param string $residentId
     * @param string $externalUnitId
     * @return null|Contract|ContractWaiting
     * @throws \Exception
     */
    protected function getContract(PropertyMapping $propertyMapping, $residentId, $externalUnitId)
    {
        $this->logMessage(
            sprintf(
                'Searching contract by property %s, externalUnitId %s, residentId %s',
                $propertyMapping->getId(),
                $externalUnitId,
                $residentId
            )
        );
        $contracts = $this
            ->getContractRepository()
            ->findContractsByPropertyMappingResidentAndExternalUnitId(
                $propertyMapping,
                $residentId,
                $externalUnitId
            );
        if (count($contracts) > 1) {
            throw new \LogicException(
                sprintf(
                    'Found more than one contract with property %s, externalUnitId %s, residentId %s',
                    $propertyMapping->getExternalPropertyId(),
                    $externalUnitId,
                    $residentId
                )
            );
        }
        if (count($contracts) == 1) {
            return reset($contracts);
        }

        $contractWaiting = null;
        try {
            $contractWaiting = $this
                ->getContractWaitingRepository()
                ->findOneByPropertyMappingExternalUnitIdAndResidentId(
                    $propertyMapping,
                    $externalUnitId,
                    $residentId
                );
        } catch (\Doctrine\ORM\NonUniqueResultException $e) {
            throw new \LogicException(
                sprintf(
                    'Duplicate mapping found cannot update balance: property %s, externalUnitId %s, resident %s',
                    $propertyMapping->getExternalPropertyId(),
                    $externalUnitId,
                    $residentId
                )
            );
        }
        if ($contractWaiting) {
            return $contractWaiting;
        }

        $this->logMessage(
            sprintf(
                'Could not find any contract with external property %s, externalUnitId %s, resident %s',
                $propertyMapping->getExternalPropertyId(),
                $externalUnitId,
                $residentId
            )
        );

        return null;
    }

    /**
     * @param Value $customer
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdateBalanceAndPaymentAccepted(Value $customer, $contract)
    {
        $contract->setPaymentAccepted($customer->getPaymentAccepted());
        $contract->setIntegratedBalance($customer->getLeaseBalance());
        $this->logMessage(
            sprintf(
                '[SyncBalance]Payment accepted set to %s and balance to %s. For %s #%d',
                $contract->getPaymentAccepted(),
                $contract->getIntegratedBalance(),
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getHoldingsForUpdatingRent()
    {
        return $this->getHoldingRepository()->findHoldingsForUpdatingRentMRI();
    }

    /**
     * @param Value $customer
     * @param PropertyMapping $propertyMapping
     */
    protected function updateContractRentForResidentTransaction(
        $customer,
        PropertyMapping $propertyMapping
    ) {
        /** @var Resident $resident */
        foreach ($customer->getResidents()->getResidentArray() as $resident) {
            try {
                if (null !== $contract = $this->getContract(
                    $propertyMapping,
                    $resident->getResidentId(),
                    $customer->getExternalUnitId()
                )) {
                    $this->logMessage(
                        sprintf(
                            '[SyncRent]Processing %s #%d for resident "%s".',
                            (new \ReflectionObject($contract))->getShortName(),
                            $contract->getId(),
                            $resident->getResidentId()
                        )
                    );
                    $this->doUpdateRent($customer, $contract);
                }
            } catch (\Exception $e) {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]ERROR: %s on %s:%d',
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    ),
                    LogLevel::ALERT
                );
            }
        }
    }

    /**
     * @param Value $customer
     * @param Contract|ContractWaiting $contract
     */
    protected function doUpdateRent(Value $customer, $contract)
    {
        $chargeCodes = $contract->getGroup()->getHolding()->getRecurringCodesArray();
        $currentCharges = $customer->getCurrentCharges();
        $charges = $currentCharges->getCharges();
        $amount = 0;
        /** @var Charge $charge */
        foreach ($charges as $charge) {
            if (strtolower($charge->getFrequency()) !== 'm') {
                $this->logMessage(sprintf('[SyncRent]Frequency not equals "m" it "%s"', $charge->getFrequency()));
                continue;
            }

            $chargeCode = $charge->getChargeCode();
            if (!in_array($chargeCode, $chargeCodes) && !empty($chargeCodes)) {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Charge code(%s) not in list (%s)',
                        $chargeCode,
                        $contract->getGroup()->getHolding()->getRecurringCodes()
                    )
                );
                continue;
            }

            $effectiveDate = $charge->getDateTimeEffectiveDate();
            $endDate = $charge->getDateTimeEndDate();

            if (!DateChecker::nowFallsBetweenDates($effectiveDate, $endDate)) {
                $this->logMessage(
                    sprintf(
                        '[SyncRent]Today doesn\'t not fall between "%s" and "%s"',
                        $effectiveDate ? $effectiveDate->format('Y-m-d') : '',
                        $endDate ? $endDate->format('Y-m-d') : ''
                    )
                );
                continue;
            }

            $amount += $charge->getAmount();
        }

        if ($amount === 0) {
            $this->logMessage('[SyncRent]Amount is 0');

            return;
        }

        $contract->setRent($amount);
        $this->logMessage(
            sprintf(
                '[SyncRent]Rent for %s #%d updated to %s',
                (new \ReflectionObject($contract))->getShortName(),
                $contract->getId(),
                $amount
            )
        );
    }
}
