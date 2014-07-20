<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use RentJeeves\CoreBundle\DateTime;
use RentJeeves\CoreBundle\Traits\DateCommon;
use RentJeeves\TenantBundle\Model\CreditTrack;

class PaymentAccountRepository extends EntityRepository
{
    use DateCommon;

    /**
     * @param int $id
     *
     * @return PaymentAccount
     */
    public function findOneWithGroupAddress($id)
    {
        $queryBuilder = $this->createQueryBuilder('pa');
        $queryBuilder->leftJoin('pa.address', 'a');
        $queryBuilder->where('pa.id = :id');
        $queryBuilder->setParameter('id', $id);

        return $queryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @return array
     */
    public function collectCreditTrackToJobs()
    {
        $date = new DateTime();
        $query = $this->createQueryBuilder('pa');
        $query->innerJoin('pa.creditTrackUserSetting', 'us');
        $query->andWhere('DATE(us.creditTrackEnabledAt) < :date');
        $query->setParameter('date', $date->format('Y-m-d'));
        $query->andWhere('DAY(us.creditTrackEnabledAt) IN (:dueDays)');
        $query->setParameter('dueDays', $this->getDueDays(0, $date));

        $paymentAccounts = $query->getQuery()->execute();

        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        $jobs = array();
        /** @var PaymentAccount $paymentAccount */
        foreach ($paymentAccounts as $paymentAccount) {
            $job = new Job('payment:pay', array('--app=rj'));
            $relatedEntity = new JobRelatedCreditTrack();
            $relatedEntity->setCreditTrackPaymentAccount($paymentAccount);
            $job->addRelatedEntity($relatedEntity);
            $em->persist($jobs[] = $job);
        }
        $em->flush();
        return $jobs;
    }
}
