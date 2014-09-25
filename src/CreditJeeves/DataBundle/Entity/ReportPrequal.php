<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Score;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\ReportPrequalRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ReportPrequal extends Report
{
}
