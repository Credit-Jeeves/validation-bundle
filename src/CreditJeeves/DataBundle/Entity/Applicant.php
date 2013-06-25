<?php
namespace CreditJeeves\DataBundle\Entity;

use CreditJeeves\DataBundle\Enum\UserType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Applicant extends User
{
    /**
     * @var string
     */
    protected $type = UserType::APPLICANT;

    public function getCurrentScore()
    {
        $score = $this->getScores()->last();
        return $score ? $score->getScore() : 0;
    }
}
