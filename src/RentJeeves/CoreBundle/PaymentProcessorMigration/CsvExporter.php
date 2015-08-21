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
     * @param AciProfileMapper $mapper
     * @param Serializer $serializer
     * @param Validator $validator
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
     * @param string $pathToFile
     * @param array $holdings
     */
    public function export($pathToFile, array $holdings = null)
    {
        if ($holdings === null) {
            $aciProfiles = $this->getAciProfileMapRepository()->findAll();
        } else {
            $aciProfiles = $this->getAciProfileMapRepository()->findAllByHoldingIds($this->getHoldingIds($holdings));
        }
        if (true === empty($aciProfiles)) {
            return;
        }

        $models = $this->mapProfilesToModels($aciProfiles, $holdings);
        $models = $this->getValidModels($models);
        $csvData = $this->serializeModelsToCsv($models);

        file_put_contents($pathToFile, $csvData);
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
     * @param array $holdings
     *
     * @return array
     */
    protected function getHoldingIds(array $holdings)
    {
        $ids = [];
        /** @var Holding $holding */
        foreach ($holdings as $holding) {
            $ids[] = $holding->getId();
        }

        return $ids;
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
