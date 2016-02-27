<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\ExternalApiBundle\Services\MRI\ResidentDataManager as MRIResidentDataManager;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ApiExtractorInterface;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Traits\SetupExternalPropertyIdTrait;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Traits\SetupGroupTrait;

/**
 * Service`s name "import.property.extractor.mri"
 */
class MRIExtractor implements ApiExtractorInterface
{
    use SetupGroupTrait;
    use SetupExternalPropertyIdTrait;

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
    public function extractData()
    {
        if (null === $this->group || null === $this->externalPropertyId) {
            throw new ImportLogicException(
                'Pls configure extractor("setGroup","setExtPropertyId") before extractData.'
            );
        }
        $this->logger->info(
            sprintf(
                'Starting process MRI extractData for extPropertyId#%s',
                $this->externalPropertyId
            ),
            ['group_id' => $this->group->getId()]
        );

        if (!$this->group->getIntegratedApiSettings() instanceof MRISettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for MRIExtractor.',
                ['group_id' => $this->group->getId()]
            );

            throw new ImportExtractorException($message);
        }

        $this->residentDataManager->setSettings($this->group->getIntegratedApiSettings());

        try {
            $data = $this->residentDataManager->getResidentTransactions($this->externalPropertyId);
        } catch (\Exception $e) {
            $this->logger->warning(
                $message = sprintf(
                    'Can`t get data from MRI for ExternalPropertyId="%s". Details: %s',
                    $this->externalPropertyId,
                    $e->getMessage()
                ),
                ['group_id' => $this->group->getId()]
            );

            throw new ImportExtractorException($message);
        }

        if (empty($data)) {
            $this->logger->info(
                sprintf(
                    'Returned response for extPropertyId#%s is empty.',
                    $this->externalPropertyId
                ),
                ['group_id' => $this->group->getId()]
            );
        }

        $this->logger->info(
            sprintf(
                'Finished process extractData for extPropertyId#%s',
                $this->externalPropertyId
            ),
            ['group_id' => $this->group->getId()]
        );

        return $data;
    }
}
