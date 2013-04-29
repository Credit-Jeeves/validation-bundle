<?php
namespace CreditJeeves\ExperianBundle\Converter;

use CreditJeeves\CoreBundle\Arf\ArfParser;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Atb as AtbEntity;
use CreditJeeves\ExperianBundle\Model\Atb as Model;

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
     * @var ArfParser
     */
    protected $arfParser;

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
            ->setBlocks($this->getBlocks($entity))
            ->setTitle($this->getTitle())
            ->setTitleMessage($this->getTitleMessage())
        ;


        return $this->model;
    }

    protected function trans($string, $parameters = array())
    {
        return $this->translator->trans($string, $parameters, 'simulation');
    }

    protected function getBlocks($entity)
    {
        $result = $entity->getResult();
        return $result['blocks'];
    }

    protected function getPlaceHolders()
    {
        return array(
            '%STEPS%' => count($this->model->getBlocks()),
            '%POINTS_INCREASE%' => $this->model->getScoreBest() - $this->scoreCurrent,
            '%CASH_USED%' => $this->cash_used,
            '%SCORE_BEST%' => $this->score_best,
            '%CASH%' => $this->input,
            '%TIER%' => $this->tierBest
        );
    }

    protected function getTitle()
    {
        $placeHolders = $this->getPlaceHolders();

        if ($this->getAllBlocks()) {
            if ('score' == $this->type) {
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
            } else if ('cash' == $this->type) {
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
        } else if ('cash' == $this->type) {
            return $this->trans(
                "cash-not-reach-title-message-%CASH%",
                $placeHolders,
                'simulation'
            );
        } else if ($this->scoreCurrent > $this->scoreTarget) {
            return $this->trans(
                "reached-score-title-message",
                $placeHolders,
                'simulation'
            );
        } else if ('score' == $this->type) {
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
            } else if ('cash' == $this->type) {
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
        } else if ($this->scoreCurrent > $this->scoreTarget) {
            return $this->trans(
                "reached-score-title-sub-message",
                $placeHolders,
                'simulation'
            );
        } else if ('score' == $this->type) {
            return $this->trans(
                "score-not-reach-title-sub-message",
                $placeHolders,
                'simulation'
            );
        }
        return '';
    }
}
