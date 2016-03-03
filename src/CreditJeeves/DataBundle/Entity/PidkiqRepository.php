<?php

namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\PidkiqStatus;
use Doctrine\ORM\EntityRepository;
use RentJeeves\CoreBundle\DateTime;

class PidkiqRepository extends EntityRepository
{
    /**
     * @param int $id
     * @param User $user
     * @param int $lifeTimeOnMinutes
     * @return Pidkiq|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findNotExpiredByUserAndId($id, User $user, $lifeTimeOnMinutes = 10)
    {
        $datetime = (new DateTime())->modify('-' . (int) $lifeTimeOnMinutes . ' minutes')->format('Y-m-d H:i:s');

        return $this->createQueryBuilder("p")
            ->where("p.id = :id")
            ->andWhere("p.user = :user")
            ->andWhere("STR_TO_DATE(p.created_at, '%Y-%c-%e %T') > STR_TO_DATE(:datetime, '%Y-%c-%e %T')")
            ->setParameters([':id' => $id, ':user' => $user, ':datetime' => $datetime])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param User $user
     * @return Pidkiq|null
     */
    public function findLastSuccessSessionByUser(User $user)
    {
        return $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->andWhere('p.status = :successStatus')
            ->setParameters([':user' => $user, ':successStatus' => PidkiqStatus::SUCCESS])
            ->orderBy('p.updated_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
