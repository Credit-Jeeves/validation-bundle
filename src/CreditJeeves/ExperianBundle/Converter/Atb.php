<?php
namespace CreditJeeves\ExperianBundle\Converter;

use CreditJeeves\CoreBundle\Arf\ArfParser;
use CreditJeeves\CoreBundle\Arf\ArfReport;
use CreditJeeves\DataBundle\Enum\AtbType;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\DataBundle\Entity\Atb as AtbEntity;
use CreditJeeves\ExperianBundle\Model\Atb as Model;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use CreditJeeves\ExperianBundle\Exception\Atb as AtbException;

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

    public function getModel(AtbEntity $entity, ArfParser $arfParser, $targetScore)
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
            ->setScoreTarget($targetScore)
            ->setSimTypeGroup($this->getSimTypeGroup())
            ->setTitle($this->getTitle())
            ->setTitleMessage($this->getTitleMessage())
            ->setBlocks($this->getBlocks($entity));


        return $this->model;
    }

    protected function trans($string, $parameters = array())
    {
        return $this->translator->trans($string, $parameters, 'simulation');
    }

    protected function getSimTypeGroup()
    {
        $simType = $this->model->getSimType();
        $simType[2] = 'x';
        return $simType;
    }

    /**
     * @param AtbEntity $entity
     *
     * @return array
     */
    protected function getBlocks($entity)
    {
        $result = $entity->getResult();
        $blocks = array();
        $subMessage = null;
        $links = array();
        foreach($result['blocks'] as $val) {
            switch($this->model->getSimTypeGroup())
            {
                case '10x':
                case '20x':
                    $message = $this->model->getSimTypeGroup() .
                    '-message-%BANK%-%ACCOUNT%-%CASH_DIFF%-%BALANCE%-%AMOUNT1%';
                    $subMessage = $this->model->getSimTypeGroup() . '-sub-message-%BANK%-%BANK_PHONE%';
                    break;
                case '30x':
                case '40x':
                    $message = $this->model->getSimTypeGroup() . '-message-%BALANCE%';
                    if (!empty($val['banks'])) {
                        $subMessage = $this->model->getSimTypeGroup() . '-sub-message-%BANKS%';
                    }
                    break;
                default:
                    throw new AtbException($this->model->getSimTypeGroup() .  "sim type group does not found");
            }
            $blocks[] = array(
                'message' => $this->trans(
                    $message,
                    $this->getPlaceHoldersForBlock($val)
                ),
                'sub_message' => !$subMessage ? null : $this->trans(
                    $subMessage,
                    $this->getPlaceHoldersForBlock($val)
                ),
                'links' => $links,
            );
        }
        return $blocks;
    }

    protected function getPlaceHoldersForBlock($block)
    {
        $return = array();
        foreach($block as $key => $val) {
            $return['%' . strtoupper($key) . '%'] = $val;
        }
        return $return;
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
            if (AtbType::SCORE == $this->model->getType()) {
                if ($this->model->getScoreTarget() < $this->model->getScoreBest()) {
                    return $this->trans(
                        "score-reach-title-message-%CASH_USED%",
                        $placeHolders
                    );
                } else {
                    return $this->trans(
                        $this->model->getIsDealerSide() ?
                        "dealer-score-search-reach-title-message-%TIER%":
                        "score-search-reach-title-message-%POINTS_INCREASE%",
                        $placeHolders
                    );
                }
            } elseif (AtbType::CASH == $this->model->getType()) {
                if ($this->model->getScoreTarget() < $this->model->getScoreBest()) {
                    return $this->trans(
                        "cash-reach-title-message-%POINTS_INCREASE%-%STEPS%-%CASH_USED%-%SCORE_BEST%",
                        $placeHolders
                    );
                } else {
                    return $this->trans(
                        $this->model->getIsDealerSide() ?
                        "dealer-cash-reach-title-message-%TIER%-%STEPS%-%CASH_USED%-%SCORE_BEST%":
                        "cash-increase-title-message-%POINTS_INCREASE%-%STEPS%-%CASH_USED%-%SCORE_BEST%",
                        $placeHolders
                    );
                }
            }
        } elseif (AtbType::CASH == $this->model->getType()) {
            return $this->trans(
                "cash-not-reach-title-message-%CASH%",
                $placeHolders
            );
        } elseif ($this->model->getScoreCurrent() > $this->model->getScoreTarget()) {
            return $this->trans(
                "reached-score-title-message",
                $placeHolders
            );
        } elseif (AtbType::SCORE == $this->model->getType()) {
            return $this->trans(
                "score-not-reach-title-message",
                $placeHolders
            );
        }
        return '';
    }

    protected function getTitleMessage()
    {
        $placeHolders = $this->getPlaceHolders();
        if ($this->model->getBlocks()) {
            if (AtbType::SCORE == $this->model->getType()) {
                if ($this->model->getScoreTarget() < $this->model->getScoreBest()) {
                    return $this->trans(
                        $this->model->getIsDealerSide() ?
                        "dealer-score-reach-title-sub-message-%TIER%" :
                        "score-reach-title-sub-message-%POINTS_INCREASE%",
                        $placeHolders
                    );
                } else {
                    return $this->trans(
                        "score-search-reach-title-sub-message-%CASH_USED%",
                        $placeHolders
                    );
                }
            } elseif (AtbType::CASH == $this->model->getType()) {
                if ($this->model->getScoreTarget() < $this->model->getScoreBest()) {
                    return $this->trans(
                        "cash-reach-title-sub-message",
                        $placeHolders
                    );

                } else {
                    return $this->trans(
                        "cash-increase-title-sub-message",
                        $placeHolders
                    );
                }

            }
        } elseif ($this->model->getScoreCurrent() > $this->model->getScoreTarget()) {
            return $this->trans(
                "reached-score-title-sub-message",
                $placeHolders
            );
        } elseif (AtbType::SCORE == $this->model->getType()) {
            return $this->trans(
                "score-not-reach-title-sub-message",
                $placeHolders
            );
        }
        return '';
    }
}
