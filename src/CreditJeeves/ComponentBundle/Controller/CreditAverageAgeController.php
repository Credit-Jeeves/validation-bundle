<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;

/**
 *
 * This component currently only supports Experian prequal reports
 * Transunion does not provide this information in their report snapshot
 *
 * Class CreditAverageAgeController
 * @package CreditJeeves\ComponentBundle\Controller
 */
class CreditAverageAgeController extends Controller
{

    public function indexAction(Report $Report)
    {
        $this->age = 0;

        $total = 0;
        $tradelines = $this->getTradeLines();
        $currentDate = new \DateTime('now');
        if (!empty($tradelines)) {
            foreach ($tradelines as $tradeline) {
                $total++;
                $openedDate = \DateTime::createFromFormat('my', $tradeline['date_open']);
                if (empty($openedDate)) {
                    continue;
                }
                $interval = $openedDate->diff($currentDate);
                $months = $interval->format('%y') * 12 + $interval->format('%m');
                $this->age += $months;
            }
        }
        if ($total > 0) {
            $this->age = floor($this->age / ($total * 12));
        }

        return $this->render(
            'ComponentBundle:CreditAverageAge:index.html.twig',
            array(
                'nOldest' => $Report->getOldestTradelineInYears(),
                'age' => $this->age,
            )
        );
    }
}
