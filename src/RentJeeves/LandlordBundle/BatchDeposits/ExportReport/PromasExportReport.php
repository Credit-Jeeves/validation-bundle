<?php
namespace RentJeeves\LandlordBundle\BatchDeposits\ExportReport;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\OrderRepository;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\LandlordBundle\Accounting\Export\Exception\ExportException;
use RentJeeves\LandlordBundle\Accounting\Export\Report\PromasReport;

class PromasExportReport extends PromasReport
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
            if (null !== $groups) {
                $groups = $groups->toArray();
                if (null !== $settings['groupIds'] && $groupIds = $settings['groupIds']) {
                    $groups = array_filter(
                        $groups,
                        function (Group $group) use ($groupIds) {
                            return in_array($group->getId(), $groupIds);
                        }
                    );
                }
            }
        }
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('DataBundle:Order');

        return $groups ? $orderRepository->getOrdersForPromasReport($groups, $beginDate, $endDate, $exportBy) : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateSettings(array $settings)
    {
        if (!isset($settings['landlord']) || !($settings['landlord'] instanceof Landlord) ||
            !isset($settings['begin']) || !isset($settings['end']) || !isset($settings['export_by'])
        ) {
            throw new ExportException('Not enough parameters for Promas export report');
        }
    }
}
