<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;

use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\LandlordBundle\Accounting\Import\Handler\HandlerAbstract;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Exception;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageAbstract;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service("accounting.import.factory")
 */
class ImportFactory
{
    const CSV = 'csv';

    const INTEGRATED_API = 'integrated_api';

    const BASE_NAME_STORAGE = 'accounting.import.storage';

    const BASE_NAME_MAPPING = 'accounting.import.mapping';

    const BASE_NAME_HANDLER = 'accounting.import.handler';

    protected $container;

    protected $session;

    protected $importType;

    protected $availableImportType = array(self::CSV, self::INTEGRATED_API);

    /**
     * @InjectParams({
     *     "container"          = @Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->session = $container->get('session');
        $this->importType = $this->session->get(StorageAbstract::IMPORT_STORAGE_TYPE);
    }

    /**
     * @throws Exception
     * @return HandlerAbstract
     */
    public function getHandler()
    {
        return $this->getServiceImport(
            $this->getServiceName(
                self::BASE_NAME_HANDLER,
                $this->importType
            )
        );
    }

    /**
     * @param $importType
     *
     * @return string
     */
    public function getImportType($importType)
    {
        if (empty($importType)) {
            return $this->importType;
        }

        if ($importType === self::INTEGRATED_API) {
            return $this->getAccountingSettingType();

        }

        return $importType;
    }

    /**
     * @param null $importType
     * @throws Exception
     */
    public function getStorage($importType = null)
    {
        return $this->getServiceImport(
            $this->getServiceName(
                self::BASE_NAME_STORAGE,
                $this->getImportType($importType)
            )
        );
    }

    public function getMapping()
    {
        return $this->getServiceImport(
            $this->getServiceName(
                self::BASE_NAME_MAPPING,
                $this->importType
            )
        );
    }

    public function clearSessionAllImports()
    {
        foreach ($this->availableImportType as $type) {
            if ($this->getAccountingSettingType() === ApiIntegrationType::NONE && $type === self::INTEGRATED_API) {
                continue;
            }

            $storage = $this->getStorage($type);
            $storage->clearSession();
        }
    }

    /**
     * @param $service
     * @throws Exception
     */
    protected function getServiceImport($service)
    {
        if ($this->container->has($service)) {
            return $this->container->get($service);
        }

        throw new Exception(
            sprintf(
                'Such service "%s" for import does not exist',
                $service
            )
        );
    }

    /**
     * @param $baseName
     * @param $typeService
     *
     * @return string
     */
    protected function getServiceName($baseName, $typeService)
    {
        return sprintf('%s.%s', $baseName, $typeService);
    }

    protected function getAccountingSettingType()
    {
        /** @var $user Landlord */
        $user = $this->container->get('security.context')->getToken()->getUser();
        /** @var $holding Holding */
        $holding = $user->getHolding();
        $accountingSettings = $holding->getAccountingSettings();
        if (empty($accountingSettings)) {
            return ApiIntegrationType::NONE;
        }

        return ApiIntegrationType::$importMapping[$accountingSettings->getApiIntegration()];
    }
}
