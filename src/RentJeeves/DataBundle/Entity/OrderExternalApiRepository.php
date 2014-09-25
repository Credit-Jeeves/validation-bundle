<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use DateTime;

class OrderExternalApiRepository extends EntityRepository
{
    /**
     * @param DateTime $date
     * @param $apiType
     */
    public function removeByDateAndApiType(DateTime $date, $apiType)
    {
        $this->createQueryBuilder('api')
            ->delete()
            ->where('api.depositDate = :date')
            ->andWhere('api.apiType = :apiType')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('apiType', $apiType)
            ->getQuery()
            ->execute();
    }
}
