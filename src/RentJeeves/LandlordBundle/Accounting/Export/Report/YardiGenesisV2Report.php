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


    protected function generateFilename($params)
    {
        $beginDate = new DateTime($params['begin']);
        $endDate = new DateTime($params['end']);

        $this->filename = sprintf(
            'YardiGenesisV2_%s_%s.csv',
            $beginDate->format('Ymd'),
            $endDate->format('Ymd')
        );
    }
}
