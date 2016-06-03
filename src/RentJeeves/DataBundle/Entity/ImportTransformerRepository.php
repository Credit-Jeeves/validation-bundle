<?php
namespace RentJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;

class ImportTransformerRepository extends EntityRepository
{
    /**
     * @param Group  $group
     * @param string $externalPropertyId
     * @param string $importType
     *
     * @return string|null
     */
    public function findClassNameWithPriorityByGroupAndExternalPropertyId(
        Group $group,
        $externalPropertyId,
        $importType
    ) {
        $result = $this->createQueryBuilder('it')
            ->select('it.className')
            ->where('it.holding = :holding AND it.externalPropertyId = :externalPropertyId AND it.group is NULL')
            ->andWhere('it.importType = :importType')
            ->orWhere('it.group = :group AND it.holding is NULL AND it.externalPropertyId is NULL')
            ->orWhere('it.holding = :holding AND it.group is NULL AND it.externalPropertyId is NULL')
            ->orderBy('it.externalPropertyId', 'desc')
            ->addOrderBy('it.group', 'desc')
            ->setParameter('holding', $group->getHolding())
            ->setParameter('group', $group)
            ->setParameter('externalPropertyId', $externalPropertyId)
            ->setParameter('importType', $importType)
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        return empty($result) ? null : $result[0]['className'];
    }
}
