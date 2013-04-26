<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\GroupIncentive as BaseGroupIncentive;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\GroupIncentiveRepository")
 * @ORM\Table(name="cj_group_incentives")
 * @ORM\HasLifecycleCallbacks()
 */
class GroupIncentive extends BaseGroupIncentive
{
}
