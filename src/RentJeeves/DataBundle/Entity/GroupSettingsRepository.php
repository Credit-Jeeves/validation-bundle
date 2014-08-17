<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RentJeeves\DataBundle\Enum\PaymentStatus;
use RentJeeves\DataBundle\Enum\PaymentType;

/**
 * GroupSettings
 */
class GroupSettingsRepository extends EntityRepository
{
    public function hasReccuringPayment($groupSettingId)
    {
        $query = $this->createQueryBuilder('setting');
        $query->innerJoin('setting.group', 'group');
        $query->innerJoin('group.contracts', 'contract');
        $query->innerJoin('contract.payments', 'payment');
        $query->where('setting.id = :settingId');
        $query->andWhere('payment.status = :paymentStatus');
        $query->andWhere('payment.type = :type');

        $query->setParameter('paymentStatus', PaymentStatus::ACTIVE);
        $query->setParameter('settingId', $groupSettingId);
        $query->setParameter('type', PaymentType::RECURRING);
        $query->setMaxResults(1);

        if ($result = $query->getQuery()->execute()) {
            return true;
        }

        return false;
    }
}
