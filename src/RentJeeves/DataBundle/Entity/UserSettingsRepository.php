<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Holding;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\EntityRepository;
use RentJeeves\CoreBundle\Traits\DateCommon;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Doctrine\ORM\Query\Expr;

class UserSettingsRepository extends EntityRepository
{
    use DateCommon;

    /**
     * @return UserSettings[]
     */
    public function getUserSettingsForCreditTrackByTodayDueDay()
    {
        $date = new \DateTime();
        $query = $this->createQueryBuilder('settings')
            ->innerJoin('settings.creditTrackPaymentAccount', 'pa')
            //Payment which setup today must not be executed
            ->andWhere('DATE(settings.creditTrackEnabledAt) < :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->andWhere('DAY(settings.creditTrackEnabledAt) IN (:dueDays)')
            ->setParameter('dueDays', $this->getDueDays(0, $date));

        return $query->getQuery()->execute();
    }
}
