<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\EntityRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("data.entity.repository")
 */
class AtbRepository extends EntityRepository
{
}
