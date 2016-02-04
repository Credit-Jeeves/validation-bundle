<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\ResManSettings;
use RentJeeves\ExternalApiBundle\Services\ResMan\ResidentDataManager as ResmanResidentDataManager;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;

/**
 * Service`s name "import.property.extractor.resman"
 */
class ResmanExtractor implements ExtractorInterface
{
    /**
     * @var ResmanResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ResmanResidentDataManager $residentDataManager
     * @param LoggerInterface        $logger
     */
    public function __construct(ResmanResidentDataManager $residentDataManager, LoggerInterface $logger)
    {
        $this->residentDataManager = $residentDataManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function extractData(Group $group, $externalPropertyId)
    {
        $this->logger->info(
            sprintf(
                'Starting process Resman extractData for extPropertyId#%s',
                $externalPropertyId
            ),
            ['group_id' => $group->getId()]
        );

        if (!$group->getIntegratedApiSettings() instanceof ResManSettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for ResmanExtractor.',
                ['group_id' => $group->getId()]
            );

            throw new ImportExtractorException($message);
        }

        $this->residentDataManager->setSettings($group->getIntegratedApiSettings());

        try {
            $data = $this->residentDataManager->getResidentTransactions($externalPropertyId);
        } catch (\Exception $e) {
            $this->logger->warning(
                $message = sprintf(
                    'Can`t get data from Resman for ExternalPropertyId="%s". Details: %s',
                    $externalPropertyId,
                    $e->getMessage()
                ),
                ['group_id' => $group->getId()]
            );

            throw new ImportExtractorException($message);
        }

        if (empty($data)) {
            $this->logger->info(
                sprintf(
                    'Returned response for extPropertyId#%s is empty.',
                    $externalPropertyId
                ),
                ['group_id' => $group->getId()]
            );
        }

        $this->logger->info(
            sprintf(
                'Finished process extractData for extPropertyId#%s',
                $externalPropertyId
            ),
            ['group_id' => $group->getId()]
        );

        return $data;
    }
}
