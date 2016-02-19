<?php

namespace RentJeeves\ImportBundle\PropertyImport;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\ExternalApiBundle\Services\Yardi\Clients\ResidentTransactionsClient;
use RentJeeves\ImportBundle\Exception\ImportLogicException;

/**
 * Service`s name 'import.property.settings_provider'
 */
class ImportPropertySettingsProvider
{
    const YARDI_ALL_EXTERNAL_PROPERTY_IDS = '*';

    /**
     * @var ResidentTransactionsClient
     */
    protected $yardiConfigurationClient;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ResidentTransactionsClient $client
     * @param LoggerInterface            $logger
     */
    public function __construct(ResidentTransactionsClient $client, LoggerInterface $logger)
    {
        $this->yardiConfigurationClient = $client;
        $this->logger = $logger;
    }

    /**
     * @param Group $group
     *
     * @throws ImportLogicException if can`t get ExternalPropertyIds
     *
     * @return array
     */
    public function provideExternalPropertyIds(Group $group)
    {
        switch ($group->getHolding()->getAccountingSystem()) {
            case AccountingSystem::AMSI:
            case AccountingSystem::MRI:
            case AccountingSystem::RESMAN:
                return $this->getExternalPropertyIdsFromDb($group);
                break;
            case AccountingSystem::YARDI_VOYAGER:
                return $this->getExternalPropertyIdsForYardi($group);
                break;
            default:
                throw new ImportLogicException(
                    sprintf(
                        'Function "%s" doesn`t support AccountingSystem "%s".',
                        __FUNCTION__,
                        $group->getHolding()->getAccountingSystem()
                    )
                );
                break;
        }
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    protected function getExternalPropertyIdsFromDb(Group $group)
    {
        $this->logger->info('Getting External Property Ids from db.', ['group_id' => $group->getId()]);
        $allExtPropertyIds = $group->getImportSettings()->getApiPropertyIds();
        $result = [];
        foreach (explode(',', $allExtPropertyIds) as $extPropertyId) {
            $result[] = trim($extPropertyId);
        }

        return $result;
    }

    /**
     * @param Group $group
     *
     * @return array
     */
    protected function getExternalPropertyIdsForYardi(Group $group)
    {
        // @see https://credit.atlassian.net/browse/RT-1765
        if ($group->getImportSettings()->getApiPropertyIds() === self::YARDI_ALL_EXTERNAL_PROPERTY_IDS) {
            return $this->getExternalPropertyIdsFromYardiApi($group);
        } else {
            return $this->getExternalPropertyIdsFromDb($group);
        }
    }

    /**
     * @param Group $group
     *
     * @throws ImportLogicException if settings are empty or invalid
     *
     * @return array
     */
    protected function getExternalPropertyIdsFromYardiApi(Group $group)
    {
        $this->logger->info('Getting External Property Ids from Yardi Api.', ['group_id' => $group->getId()]);
        if (null === $settings = $group->getHolding()->getYardiSettings()) {
            $this->logger->error(
                $message = sprintf('U can`t run Yardi import for Group#%d without YardiSettings.', $group->getId()),
                ['group_id' => $group->getId()]
            );
            throw new ImportLogicException($message);
        }
        $this->yardiConfigurationClient->setSettings($settings);
        $this->yardiConfigurationClient->build();

        $result = [];
        try {
            if (null !== $response = $this->yardiConfigurationClient->getPropertyConfigurations()) {
                foreach ($response->getProperty() as $property) {
                    $result[] = $property->getCode();
                }
            }
        } catch (\SoapFault $e) {
            $this->logger->error(
                $message = sprintf(
                    'Yardi import is failed, pls check YardiSettings. Details : %s',
                    $e->getMessage()
                ),
                ['group_id' => $group->getId()]
            );
            throw new ImportLogicException($message);
        }

        return $result;
    }
}
