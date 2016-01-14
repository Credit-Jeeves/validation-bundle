<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\ExternalApiBundle\Services\MRI\ResidentDataManager as MRIResidentDataManager;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;

/**
 * Service`s name "import.property.extractor.mri"
 */
class MRIExtractor implements ExtractorInterface
{
    /**
     * @var MRIResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param MRIResidentDataManager $residentDataManager
     * @param LoggerInterface        $logger
     */
    public function __construct(MRIResidentDataManager $residentDataManager, LoggerInterface $logger)
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
                'Starting process extractData for extPropertyId#%s',
                $externalPropertyId
            ),
            ['group_id' => $group->getId()]
        );

        if (!$group->getIntegratedApiSettings() instanceof MRISettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for MRIExtractor.',
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
                    'Can`t get data from MRI for ExternalPropertyId="%s". Details: %s',
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
