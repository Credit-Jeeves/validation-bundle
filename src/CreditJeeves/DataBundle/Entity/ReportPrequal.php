<?php
namespace CreditJeeves\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\CoreBundle\Utility\Encryption;

/**
 * @ORM\Entity
 */
class ReportPrequal extends Report
{
    /**
     * @ORM\ManyToOne(targetEntity="CreditJeeves\UserBundle\Entity\User", inversedBy="reportsPrequal")
     * @ORM\JoinColumn(name="cj_applicant_id", referencedColumnName="id")
     */
    protected $user;
//     /**
//      * @var integer
//      */
//     protected $id;

}