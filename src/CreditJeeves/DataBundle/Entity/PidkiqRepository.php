<?php

namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PidkiqRepository extends EntityRepository
{
    /**
     * @param int $id
     * @param User $user
     * @param string $lifeTime
     * @return Pidkiq|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findNotExpiredByUserAndId($id, User $user, $lifeTime = '10 minutes')
    {
        $datetime  = (new \DateTime())->modify('-' . $lifeTime)->format('Y-m-d H:i:s');

        return $this->createQueryBuilder("p")
            ->where("p.id = :id")
            ->andWhere("p.user = :user")
            ->andWhere("STR_TO_DATE(p.created_at, '%Y-%c-%e %T') > STR_TO_DATE(:datetime, '%Y-%c-%e %T')")
            ->setParameters([':id' => $id, ':user' => $user, ':datetime' => $datetime])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
