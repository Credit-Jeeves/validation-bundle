<?php

namespace RentJeeves\LandlordBundle\Accounting\Export;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;

/**
 * @Service("accounting.export")
 */
class Export
{
    protected $container;

    protected $type;

    protected $makeZip;

    /**
     * @InjectParams({
     *     "container" = @Inject("service_container")
     * })
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function setupData($dataFromForm)
    {
        $this->type = $dataFromForm['type'];
        $this->makeZip = $dataFromForm['makeZip'];
    }

    public function getReport($dataFromForm)
    {
        $this->setupData($dataFromForm);
        if ($this->makeZip) {
            $serviceName = sprintf('accounting.export.%s_archive', $this->type);
        } else {
            $serviceName = sprintf('accounting.export.%s', $this->type);
        }

        if (!$this->container->has($serviceName)) {
            throw new RuntimeException('Report type('.$this->type.') is invalid.');
        }

        return $this->container->get($serviceName);
    }
}
