<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use Doctrine\ORM\EntityManager;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;
use DateTime;

class YardiGenesisV2Report extends YardiGenesisReport
{

    public function __construct(EntityManager $em, ExportSerializer $serializer, $softDeleteableControl)
    {
        parent::__construct($em, $serializer, $softDeleteableControl);
        $this->type = 'yardi_genesis_v2';
    }

    public function getData($settings)
    {
        $this->softDeleteableControl->disable();

        $this->validateSettings($settings);

        $beginDate = $settings['begin'];
        $endDate = $settings['end'];
        $orderRepository = $this->em->getRepository('DataBundle:Order');

        return $orderRepository->getOrdersForYardiGenesis($beginDate, $endDate);
    }

    protected function generateFilename($params)
    {
        $this->filename = 'PayProcV2.csv';
    }

    protected function validateSettings($settings)
    {
        if (!isset($settings['begin']) || !isset($settings['end'])) {
            throw new ExportException('Not enough parameters for Yardi Genesis V2 report');
        }
    }
}
