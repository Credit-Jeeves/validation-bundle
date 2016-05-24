<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\MRISettings;
use RentJeeves\ExternalApiBundle\Services\MRI\ResidentDataManager as MRIResidentDataManager;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ApiPropertyExtractorInterface;
use RentJeeves\ImportBundle\Traits\SetupExternalPropertyIdTrait;
use RentJeeves\ImportBundle\Traits\SetupGroupTrait;

/**
 * Service`s name "import.property.extractor.mri"
 */
class MRIExtractor implements ApiPropertyExtractorInterface
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
            'Starting process MRI extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        if (!$this->group->getIntegratedApiSettings() instanceof MRISettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for MRIExtractor.',
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );

            throw new ImportExtractorException($message);
        }

        $this->residentDataManager->setSettings($this->group->getIntegratedApiSettings());

        try {
            $data = $this->residentDataManager->getResidentTransactions($this->externalPropertyId);
        } catch (\Exception $e) {
            $this->logger->warning(
                $message = sprintf(
                    'Can`t get data from MRI. Details: %s',
                    $e->getMessage()
                ),
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );

            throw new ImportExtractorException($message);
        }

        if (empty($data)) {
            $this->logger->info(
                'Returned response is empty.',
                ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
            );
        }

        $this->logger->info(
            'Finished process extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        return $data;
    }
}
