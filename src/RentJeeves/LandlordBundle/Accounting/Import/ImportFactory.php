<?php

namespace RentJeeves\LandlordBundle\Accounting\Import;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Exception;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageAbstract;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.factory")
 */
class ImportFactory
{
    protected $container;

    protected $session;

    protected $importType;

    protected $availableImportType = array('csv', 'yardi');

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

    public function getHandler()
    {
        $serviceName = 'accounting.import.handler.'.$this->importType;

        return $this->getServiceImport($serviceName);
    }

    public function getStorage($importType = null)
    {
        if (!empty($importType)) {
            $serviceName = 'accounting.import.storage.' . $importType;
        } else {
            $serviceName = 'accounting.import.storage.' . $this->importType;
        }

        return $this->getServiceImport($serviceName);
    }

    public function getMapping()
    {
        $serviceName = 'accounting.import.mapping.'.$this->importType;

        return $this->getServiceImport($serviceName);
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

    public function clearSessionAllImports()
    {
        foreach ($this->availableImportType as $type) {
            $storage = $this->getStorage($type);
            $storage->clearSession();
        }
    }
}
