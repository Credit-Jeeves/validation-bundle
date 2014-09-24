<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ReportD2c extends Report
{
    /**
     * @ORM\OneToOne(
     *     targetEntity="\CreditJeeves\DataBundle\Entity\Operation",
     *     mappedBy="reportD2c",
     *     cascade={"persist", "remove", "merge"},
     *     orphanRemoval=true
     * )
     */
    protected $operation;
}
