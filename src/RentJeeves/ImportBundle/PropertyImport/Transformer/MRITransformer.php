<?php

namespace RentJeeves\ImportBundle\PropertyImport\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportProperty;
use RentJeeves\ExternalApiBundle\Model\MRI\Value;

/**
 * Service`s name "import.property.transformer.mri"
 */
class MRITransformer implements TransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     */
    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function transformData(array $accountingSystemData, Import $import)
    {
        /** @var Value $accountingSystemRecord */
        foreach ($accountingSystemData as $accountingSystemRecord) {
            $address = $accountingSystemRecord->getAddress() ?
                $accountingSystemRecord->getAddress() : $accountingSystemRecord->getBuildingAddress();

            $importProperty = new ImportProperty();
            $importProperty->setImport($import);
            $importProperty->setExternalPropertyId($accountingSystemRecord->getPropertyId());
            $importProperty->setExternalBuildingId($accountingSystemRecord->getBuildingId());
//            $importProperty->setAddressHasUnits(false); //PLS check
//            $importProperty->setPropertyHasBuildings(false); //PLS check
            $importProperty->setUnitName($accountingSystemRecord->getUnitId());
            $importProperty->setExternalUnitId($accountingSystemRecord->getExternalUnitId());
            $importProperty->setAddress1($address);
            $importProperty->setCity($accountingSystemRecord->getCity());
            $importProperty->setState($accountingSystemRecord->getState());
            $importProperty->setZip($accountingSystemRecord->getZipCode());

            $this->em->persist($importProperty);
        }

        $this->em->flush();
    }
}
