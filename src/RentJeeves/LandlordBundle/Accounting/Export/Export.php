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

        switch ($this->type) {
            case 'xml':
                if ($dataFromForm['makeZip']) {
                    $report = $this->container->get('accounting.export.yardi_archive');
                } else {
                    $report = $this->container->get('accounting.export.yardi');
                }
                break;
            case 'csv':
                $report = $this->container->get('accounting.export.real_page');
                break;
            case 'promas':
                if ($dataFromForm['makeZip']) {
                    $report = $this->container->get('accounting.export.promas_archive');
                } else {
                    $report = $this->container->get('accounting.export.promas');
                }
                break;
            default:
                throw new RuntimeException('Report type is invalid');
        }

        return $report;
    }
}
