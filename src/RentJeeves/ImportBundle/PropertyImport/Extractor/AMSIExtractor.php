<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\AMSISettings;
use RentJeeves\ExternalApiBundle\Services\AMSI\ResidentDataManager as AMSIResidentDataManager;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;
use RentJeeves\ImportBundle\Exception\ImportLogicException;
use RentJeeves\ImportBundle\PropertyImport\Extractor\Interfaces\ApiPropertyExtractorInterface;
use RentJeeves\ImportBundle\Traits\SetupExternalPropertyIdTrait;
use RentJeeves\ImportBundle\Traits\SetupGroupTrait;

/**
 * Service`s name "import.property.extractor.amsi"
 */
class AMSIExtractor implements ApiPropertyExtractorInterface
{
    use SetupGroupTrait;
    use SetupExternalPropertyIdTrait;

    /**
     * @var AMSIResidentDataManager
     */
    protected $residentDataManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param AMSIResidentDataManager $residentDataManager
     * @param LoggerInterface         $logger
     */
    public function __construct(AMSIResidentDataManager $residentDataManager, LoggerInterface $logger)
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
                'Pls configure extractor("setGroup","setExternalPropertyId") before extractData.'
            );
        }
        $this->logger->info(
            'Starting process AMSI extractData.',
            ['group' => $this->group, 'additional_parameter' => $this->externalPropertyId]
        );

        if (!$this->group->getIntegratedApiSettings() instanceof AMSISettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for AMSIExtractor.',
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
                    'Can`t get data from AMSI. Details: %s',
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
