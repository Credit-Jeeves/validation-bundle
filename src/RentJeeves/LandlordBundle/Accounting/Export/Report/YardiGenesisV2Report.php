<?php

namespace RentJeeves\LandlordBundle\Accounting\Export\Report;

use Doctrine\ORM\EntityManager;
use RentJeeves\LandlordBundle\Accounting\Export\Serializer\ExportSerializerInterface as ExportSerializer;

class YardiGenesisV2Report extends YardiGenesisReport
{

    public function __construct(EntityManager $em, ExportSerializer $serializer, $softDeleteableControl)
    {
        parent::__construct($em, $serializer, $softDeleteableControl);
        $this->type = 'yardi_genesis_v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getData(array $settings)
    {
        $this->softDeleteableControl->disable();

        $this->validateSettings($settings);

        $beginDate = $settings['begin'];
        $endDate = $settings['end'];
        $exportBy = $settings['export_by'];
        $property = $settings['property'];
        $orderRepository = $this->em->getRepository('DataBundle:Order');
        /** @var Landlord $landlord */
        $landlord = $settings['landlord'];

        if (isset($settings['includeAllGroups']) && $settings['includeAllGroups']) {
            $groups = $landlord->getGroups($landlord->getUser())->toArray();
            $property = null;
        } else {
            $groups = [$landlord->getGroup()];
        }

        return $orderRepository->getOrdersForYardiGenesis($beginDate, $endDate, $groups, $exportBy, $property);
    }

    protected function generateFilename($params)
    {
        $this->filename = 'PayProcV2.csv';
    }

    /**
     * @param array $settings
     * @throws ExportException
     */
    protected function validateSettings(array $settings)
    {
        if (!isset($settings['begin']) || !isset($settings['end']) || !isset($settings['export_by'])
           || !array_key_exists('property', $settings)
        ) {
            throw new ExportException('Not enough parameters for Yardi Genesis V2 report');
        }
    }
}
