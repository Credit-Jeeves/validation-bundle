<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Entity\Tradeline;
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
        $this->created_at = new \DateTime();
    }

    public function createIncentive(Tradeline $tradeline, $em)
    {
        $nGroupId = $tradeline->getCjGroupId();
        $aGroupIncentives = $em->getRepository('DataBundle:GroupIncentive')->listIncentivesByGroupId($nGroupId);
        $aApplicantIncentives = $em->getRepository(
            'DataBundle:ApplicantIncentive'
        )->listIncentivesByUser($tradeline->getUser());
        $aApplicantIncentivesKeys = array();
        foreach ($aApplicantIncentives as $oApplicantIncentive) {
            $aApplicantIncentivesKeys[] = $oApplicantIncentive->getCjIncentiveId();
        }
        foreach ($aGroupIncentives as $oIncentive) {
            $nInsentiveId = $oIncentive->getId();
            if (!in_array($nInsentiveId, $aApplicantIncentivesKeys)) {
                $cjApplicantIncentive = new static();
                $cjApplicantIncentive->setUser($tradeline->getUser());
                $cjApplicantIncentive->setCjGroupIncentive($oIncentive);
                $cjApplicantIncentive->setCjTradelineId($tradeline->getId());
                $cjApplicantIncentive->setStatus($tradeline->getStatus());
                $em->persist($cjApplicantIncentive);
                $em->flush();
                return true;
            }
        }
    }
    // Legacy code
//     public function verifyIncentive($nApplicantId, $nGroupId, $nTradelineId, $sStatus)
//     {
//         $aApplicantIncentives = cjApplicantIncentivesTable::getInstance()
//->getIncentiveByApplicantAndTradelineIds($nApplicantId, $nTradelineId);
//         if ($aApplicantIncentives->count() > 0) {
//             // Incentive already exists - it would be verified
//             $cjApplicantIncentive = $aApplicantIncentives->getFirst();
//             $cjApplicantIncentive->setIsVerified(true);
//             $cjApplicantIncentive->save();
//             return true;
//         } else {
//             // Incentive doesn't exist - we'll create it
//             return $this->createIncentive($nApplicantId, $nGroupId, $nTradelineId, $sStatus);
//         }
//     }
}
