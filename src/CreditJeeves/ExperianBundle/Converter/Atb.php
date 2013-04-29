<?php
namespace CreditJeeves\ExperianBundle\Converter;

use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\CoreBundle\Arf\ArfReport;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Atb as AtbEntity;
use CreditJeeves\ExperianBundle\Model\Atb as Model;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * @DI\Service("experian.atb.converter")
 */
class Atb
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var ArfParser
     */
    protected $arfParser;

    /**
     * @var ArfReport
     */
    protected $arfReport;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator.default")
     * })
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    public function getArfParser()
    {
        return $this->arfParser;
    }

    /**
     * @return ArfReport
     */
    protected function getArfReport()
    {
        if (null == $this->arfReport) {
            $arfArray = $this->getArfParser()->getArfArray();
            $this->arfReport = new ArfReport($arfArray);
        }

        return $this->arfReport;
    }

    public function getModel(AtbEntity $entity, ArfParser $arfParser)
    {
        $this->arfParser = $arfParser;
        $result = $entity->getResult();

        $this->model = new Model();
        $this->model->setSimType($entity->getSimType())
            ->setInput($entity->getInput())
            ->setType($entity->getType())
            ->setCashUsed($result['cash_used'])
            ->setMessage($result['message'])
            ->setScoreBest($result['score_best'])
            ->setScoreInit($result['score_init'])
            // TODO find out, maybe it should be changed to lead's value
            ->setScoreCurrent($this->getArfReport()->getValue(ArfParser::SEGMENT_RISK_MODEL, ArfParser::REPORT_SCORE))
            ->setBlocks($this->getBlocks($entity))
            ->setTitle($this->getTitle())
            ->setTitleMessage($this->getTitleMessage());


        return $this->model;
    }

    protected function trans($string, $parameters = array())
    {
        return $this->translator->trans($string, $parameters, 'simulation');
    }

    /**
     * @param AtbEntity $entity
     *
     * @return array
     */
    protected function getBlocks($entity)
    {
        $result = $entity->getResult();
        return $result['blocks'];
    }

    protected function getPlaceHolders()
    {
        return array(
            '%STEPS%' => count($this->model->getBlocks()),
            '%POINTS_INCREASE%' => $this->model->getScoreBest() - $this->model->getScoreCurrent(),
            '%CASH_USED%' => $this->model->getCashUsed(),
            '%SCORE_BEST%' => $this->model->getScoreBest(),
            '%CASH%' => $this->model->getInput(),
//            '%TIER%' => $this->model->getT
        );
    }

    protected function getTitle()
    {
        $placeHolders = $this->getPlaceHolders();

        if ($this->model->getBlocks()) {
            if ('score' == $this->model->getType()) {
                if ($this->scoreTarget < $this->score_best) {
                    return $this->trans(
                        "score-reach-title-message-%CASH_USED%",
                        $placeHolders,
                        'simulation'
                    );
                } else {
                    return $this->trans(
                        $this->isDealerSide ?
                        "dealer-score-search-reach-title-message-%TIER%":
                        "score-search-reach-title-message-%POINTS_INCREASE%",
                        $placeHolders,
                        'simulation'
                    );
                }
            } elseif ('cash' == $this->type) {
                if ($this->scoreTarget < $this->score_best) {
                    return $this->trans(
                        "cash-reach-title-message-%POINTS_INCREASE%-%STEPS%-%CASH_USED%-%SCORE_BEST%",
                        $placeHolders,
                        'simulation'
                    );
                } else {
                    return $this->trans(
                        $this->isDealerSide ?
                        "dealer-cash-reach-title-message-%TIER%-%STEPS%-%CASH_USED%-%SCORE_BEST%":
                        "cash-increase-title-message-%POINTS_INCREASE%-%STEPS%-%CASH_USED%-%SCORE_BEST%",
                        $placeHolders,
                        'simulation'
                    );
                }
            }
        } elseif ('cash' == $this->type) {
            return $this->trans(
                "cash-not-reach-title-message-%CASH%",
                $placeHolders,
                'simulation'
            );
        } elseif ($this->scoreCurrent > $this->scoreTarget) {
            return $this->trans(
                "reached-score-title-message",
                $placeHolders,
                'simulation'
            );
        } elseif ('score' == $this->type) {
            return $this->trans(
                "score-not-reach-title-message",
                $placeHolders,
                'simulation'
            );
        }
        return '';
    }

    protected function getTitleMessage()
    {
        $placeHolders = $this->getPlaceHolders();
        if ($this->getAllBlocks()) {
            if ('score' == $this->type) {
                if ($this->scoreTarget < $this->score_best) {
                    return $this->trans(
                        $this->isDealerSide ?
                        "dealer-score-reach-title-sub-message-%TIER%" :
                        "score-reach-title-sub-message-%POINTS_INCREASE%",
                        $placeHolders,
                        'simulation'
                    );
                } else {
                    return $this->trans(
                        "score-search-reach-title-sub-message-%CASH_USED%",
                        $placeHolders,
                        'simulation'
                    );
                }
            } elseif ('cash' == $this->type) {
                if ($this->scoreTarget < $this->score_best) {
                    return $this->trans(
                        "cash-reach-title-sub-message",
                        $placeHolders,
                        'simulation'
                    );

                } else {
                    return $this->trans(
                        "cash-increase-title-sub-message",
                        $placeHolders,
                        'simulation'
                    );
                }

            }
        } elseif ($this->scoreCurrent > $this->scoreTarget) {
            return $this->trans(
                "reached-score-title-sub-message",
                $placeHolders,
                'simulation'
            );
        } elseif ('score' == $this->type) {
            return $this->trans(
                "score-not-reach-title-sub-message",
                $placeHolders,
                'simulation'
            );
        }
        return '';
    }
}
