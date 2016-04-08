<?php

namespace RentJeeves\CoreBundle\PaymentProcessorMigration;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Mapper\AciProfileMapper;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\AccountRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\ConsumerRecord;
use RentJeeves\CoreBundle\PaymentProcessorMigration\Model\FundingRecord;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;

class CsvExporter
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var AciProfileMapper
     */
    protected $mapper;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param EntityManagerInterface $em
     * @param AciProfileMapper       $mapper
     * @param Serializer             $serializer
     * @param Validator              $validator
     */
    public function __construct(
        EntityManagerInterface $em,
        AciProfileMapper $mapper,
        Serializer $serializer,
        Validator $validator
    ) {
        $this->em = $em;
        $this->mapper = $mapper;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * @param string     $pathToDir
     * @param string     $filePrefix
     * @param int        $profilesLimit
     * @param array|null $holdingIds
     */
    public function export($pathToDir, $filePrefix, $profilesLimit, array $holdingIds)
    {
        $aciProfiles = $this->getAciProfileMapRepository()->findAllByHoldingIds($holdingIds);

        if (true === empty($aciProfiles)) {
            return;
        }

        $batches = $this->createBatchesWithValidModels($aciProfiles, $profilesLimit, $holdingIds);
        foreach ($batches as $key => $batch) {
            $batchData = $this->serializeModelsToCsv($batch);
            $filePath = sprintf('%s/%s%s.csv', $pathToDir, $filePrefix, $key);

            file_put_contents($filePath, $batchData);
        }
    }

    /**
     * @param array      $aciProfiles
     * @param int        $batchSize
     * @param array|null $holdings
     *
     * @return array
     */
    protected function createBatchesWithValidModels(array $aciProfiles, $batchSize, array $holdingIds)
    {
        $batches = [];
        $batchIndex = 0;
        $countProfilesInBatch = 0;
        foreach ($aciProfiles as $aciProfile) {
            if (false === isset($batches[$batchIndex])) {
                $batches[$batchIndex] = [];
            }
            $aciProfileModels = $this->mapper->map($aciProfile, $holdingIds);
            $aciProfileValidModels = $this->getValidModels($aciProfileModels);
            if (false === empty($aciProfileValidModels)) {
                $batches[$batchIndex] = array_merge($batches[$batchIndex], $aciProfileValidModels);
                $countProfilesInBatch++;
            }

            if ($countProfilesInBatch !== 0 && $countProfilesInBatch % $batchSize === 0) {
                $batchIndex++;
                $countProfilesInBatch = 0;
            }
        }

        return $batches;
    }

    /**
     * @param array $aciProfiles
     * @param array $holdings
     *
     * @return array
     */
    protected function mapProfilesToModels(array $aciProfiles = [], array $holdings = null)
    {
        $models = [];
        foreach ($aciProfiles as $aciProfile) {
            $models = array_merge($models, $this->mapper->map($aciProfile, $holdings));
        }

        return $models;
    }

    /**
     * Return only valid models and add errors for not valid models
     *
     * @param array $models
     *
     * @return array
     */
    protected function getValidModels(array $models)
    {
        $validModels = [];
        /** @var AccountRecord|ConsumerRecord|FundingRecord $model */
        foreach ($models as $model) {
            $errors = $this->validator->validate($model);
            if ($errors->count() === 0) {
                $validModels[] = $model;

                continue;
            }
            /** @var ConstraintViolation $error */
            foreach ($errors as $error) {
                $classNameParts = explode('\\', get_class($model));
                $className = end($classNameParts);
                $this->errors[$model->getProfileId()][] = sprintf(
                    '[%s] %s (%s): %s',
                    $className,
                    $error->getInvalidValue(),
                    $error->getPropertyPath(),
                    $error->getMessage()
                );
            }
        }

        return $validModels;
    }

    /**
     * @param array $models
     *
     * @return string
     */
    protected function serializeModelsToCsv(array $models)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setAttribute('use_header', false);

        return $this->serializer->serialize($models, 'csv_pipe', $context);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return \RentJeeves\DataBundle\Entity\AciImportProfileMapRepository
     */
    protected function getAciProfileMapRepository()
    {
        return $this->em->getRepository('RjDataBundle:AciImportProfileMap');
    }
}
