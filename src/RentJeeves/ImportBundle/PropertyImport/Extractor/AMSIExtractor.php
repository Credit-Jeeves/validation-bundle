<?php

namespace RentJeeves\ImportBundle\PropertyImport\Extractor;

use CreditJeeves\DataBundle\Entity\Group;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\AMSISettings;
use RentJeeves\ExternalApiBundle\Services\AMSI\ResidentDataManager as AMSIResidentDataManager;
use RentJeeves\ImportBundle\Exception\ImportExtractorException;

/**
 * Service`s name "import.property.extractor.amsi"
 */
class AMSIExtractor implements ExtractorInterface
{
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
    public function extractData(Group $group, $externalPropertyId)
    {
        $this->logger->info(
            sprintf(
                'Starting process AMSI extractData for extPropertyId#%s',
                $externalPropertyId
            ),
            ['group_id' => $group->getId()]
        );

        if (!$group->getIntegratedApiSettings() instanceof AMSISettings) {
            $this->logger->warning(
                $message = 'Group has incorrect settings for AMSIExtractor.',
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
                    'Can`t get data from AMSI for ExternalPropertyId="%s". Details: %s',
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
