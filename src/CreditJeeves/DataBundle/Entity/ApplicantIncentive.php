<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\ApplicantIncentive as BaseApplicantIncentive;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\ApplicantIncentiveRepository")
 * @ORM\Table(name="cj_applicant_incentives")
 * @ORM\HasLifecycleCallbacks()
 */
class ApplicantIncentive extends BaseApplicantIncentive
{
    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
    }
}
