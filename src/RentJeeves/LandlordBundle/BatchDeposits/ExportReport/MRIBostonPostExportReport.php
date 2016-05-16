<?php

namespace RentJeeves\LandlordBundle\BatchDeposits\ExportReport;

use CreditJeeves\DataBundle\Entity\OrderRepository;
use RentJeeves\LandlordBundle\Accounting\Export\Report\MRIBostonPostReport;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;

class MRIBostonPostExportReport extends MRIBostonPostReport
{
    /**
     * {@inheritdoc}
     */
    public function getData(array $settings)
    {
        $this->softDeleteableControl->disable();

        $beginDate = $settings['begin'];
        $endDate = $settings['end'];
        $exportBy = $settings['export_by'];
        /** @var Landlord $landlord */
        $landlord = $settings['landlord'];

        if (isset($settings['group']) && $settings['group']) {
            $groups = [$settings['group']];
        } else {
            $groups = $landlord->getGroups();
            $groups = null !== $groups ? $groups->toArray() : null;
        }
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('DataBundle:Order');

        return $orderRepository->getOrdersForBostonPostReport($groups, $beginDate, $endDate, $exportBy);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateSettings(array $settings)
    {
        if (!isset($settings['landlord']) || !($settings['landlord'] instanceof Landlord) ||
            !isset($settings['begin']) || !isset($settings['end']) || !isset($settings['export_by'])) {
            throw new ExportException('Not enough parameters for MRIBostonPost export report');
        }
    }
}
