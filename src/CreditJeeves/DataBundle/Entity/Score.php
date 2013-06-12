<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Model\Score as BaseScore;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CreditJeeves\DataBundle\Entity\ScoreRepository")
 * @ORM\Table(name="cj_applicant_score")
 * @ORM\HasLifecycleCallbacks()
 */
class Score extends BaseScore
{
    /**
     * @return integer
     */
    public function getFicoScore()
    {
        $nScore = $this->getScore();
        $nFicoScore = round(10 * (($nScore - 483.06) / 11.079) + 490);

        return $nFicoScore > 850 ? 850 : $nFicoScore;
    }

    /**
     * @return string
     */
    public function getScorePercentage()
    {
        $score = $this->getScore();
        if ($score >= 900) {
            return "84%";
        }
        if ($score >= 800) {
            return "64%";
        }
        if ($score >= 700) {
            return "44%";
        }
        if ($score >= 600) {
            return "19%";
        }
        if ($score >= 550) {
            return "7%";
        }
        return "2%";
    }

    /**
     * 
     * @param integer $nScore
     * @return array
     */
    public static function getTier($nScore)
    {
        if (empty($nScore)) {
            $nScore = 0;
        }
        $nTier = array('value' => 9, 'interval' => '< 300');
        if ($nScore > 724) {
            return array('value' => 1, 'interval' => '725-900');
        }
        if ($nScore > 699) {
            return array('value' => 2, 'interval' => '700-724');
        }
        if ($nScore > 674) {
            return array('value' => 3, 'interval' => '675-699');
        }
        if ($nScore > 649) {
            return array('value' => 4, 'interval' => '650-674');
        }
        if ($nScore > 599) {
            return array('value' => 5, 'interval' => '600-649');
        }
        if ($nScore > 549) {
            return array('value' => 6, 'interval' => '550-599');
        }
        if ($nScore > 499) {
            return array('value' => 7, 'interval' => '500-549');
        }
        if ($nScore > 299) {
            return array('value' => 8, 'interval' => '300-499');
        }
        return $nTier;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_date = new \DateTime();
    }

    public function __toString()
    {
        $score = $this->getScore();
        return $score;
    }
}
