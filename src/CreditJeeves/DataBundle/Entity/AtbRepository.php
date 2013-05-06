<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\Query\Expr\Andx;

/**
 * @DI\Service("data.entity.repository")
 */
class AtbRepository extends EntityRepository
{
    /**
     * @param int $reportId
     * @param int $targetScore
     * @param array $type
     * @param int $input
     *
     * @return Atb | null
     */
    public function findLatsSimulationEntity($reportId, $targetScore = null, array $type = null, $input = null)
    {
        $queryB = $this->createQueryBuilder('a');

        $whereArr = array(
            $queryB->expr()->eq('a.cj_applicant_report_id', $reportId),
//            $queryB->expr()->neq('a.transaction_signature', ''),
        );

        if (null != $targetScore) {
            $whereArr[] = $queryB->expr()->eq('a.score_target', $targetScore);
        }
        if (null !== $type) {
            $whereArr[] = $queryB->expr()->in('a.type', $type);
        }
        if (null !== $input) {
            $whereArr[] = $queryB->expr()->eq('a.input', $input);
        }

        $queryB->select()->add('where', new Andx($whereArr));

        $queryB->setMaxResults(1);

        $queryB->addOrderBy('a.id', 'DESC');

        return $queryB->getQuery()->getOneOrNullResult();
    }
}
