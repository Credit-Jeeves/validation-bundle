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
     * @var array
     */
    protected $externalUrls;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator.default"),
     *     "externalUrls" = @DI\Inject("%external_urls%")
     * })
     */
    public function __construct($translator, array $externalUrls)
    {
        $this->translator = $translator;
        $this->externalUrls = $externalUrls;
    }

    public function getModel(AtbEntity $entity)
    {
//        $this->arfParser = $arfParser;
        $result = $entity->getResult();

        $this->model = new Model();
        $this->model->setSimType($entity->getSimType())
            ->setInput($entity->getInput())
            ->setType($entity->getType())
            ->setCashUsed($result['cash_used'])
            ->setMessage($result['message'])
            ->setScoreBest($result['score_best'])
            ->setScoreInit($result['score_init'])
            ->setScoreCurrent($entity->getScoreCurrent())
            ->setScoreTarget($entity->getScoreTarget())
            ->setSimTypeGroup($this->getSimTypeGroup())
            ->setBlocks($this->getBlocks($entity))
            ->setTitle($this->getTitle())
            ->setTitleMessage($this->getTitleMessage());


        return $this->model;
    }

    protected function trans($string, $parameters = array())
    {
        return $this->translator->trans($string, $parameters, 'simulation');
    }

    protected function getSimTypeGroup()
    {
        $simType = (string)$this->model->getSimType();
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
        foreach ($result['blocks'] as $val) {
            switch($this->model->getSimTypeGroup())
            {
                case '10x':
                case '20x':
                    $message = $this->model->getSimTypeGroup() .
                    '-message-%TR_SUBNAME%-%TR_ACCTNUM%-%CASH_DIFF%-%TR_BALANCE%-%TR_AMOUNT1%';
                    $subMessage = $this->model->getSimTypeGroup() .
                    '-sub-message-%TR_SUBNAME%-%SUBSCRIBER_PHONE_NUMBER%';
                    break;
                case '30x':
                case '40x':
                    $message = $this->model->getSimTypeGroup() . '-message-%TR_BALANCE%-%SUBSCRIBER_PHONE_NUMBER%';
                    if (!empty($val['banks'])) {
                        $subMessage = $this->model->getSimTypeGroup() . '-sub-message-%BANKS%';
                    }
                    break;
                default:
                    throw new AtbException($this->model->getSimTypeGroup() .  " sim type group does not found");
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
                'links' => $this->getLinksForBlock(),
            );
        }
        return $blocks;
    }

    protected function getLinksForBlock()
    {
        $links = array();
        switch($this->model->getSimTypeGroup()) {
            case '10x':
                $links[] = array(
                    'text' => $this->trans('link-learn-more'),
                    'url' => $this->urlForUserVoice('133308-pay-down-your-balances'),
                );
                break;
            case '20x':
                $links[] = array(
                    'text' => $this->trans('link-apply-now'),
                    'url' => $this->urlForMainSite('offers'),
                );
                $links[] = array(
                    'text' => $this->trans('link-learn-more'),
                    'url' => $this->urlForUserVoice('132849-open-a-new-credit-card'),
                );
                break;
            case '30x':
                $links[] = array(
                    'text' => $this->trans('link-apply-now'),
                    'url' => $this->urlForMainSite('offers'),
                );
                $links[] = array(
                    'text' => $this->trans('link-learn-more'),
                    'url' => $this->urlForUserVoice('133574-consolidate-your-balances-to-a-new-card'),
                );
                break;
            case '40x':
                $links[] = array(
                    'text' => $this->trans('link-learn-more'),
                    'url' => $this->urlForUserVoice('133575-consolidate-your-balances-to-a-line-of-credit'),
                );
                break;
            default:
                throw new AtbException($this->model->getSimTypeGroup() .  " sim type group does not found");
        }
        return $links;
    }

    protected function getExternalUrl($key)
    {
        if (!isset($this->externalUrls[$key])) {
            throw new AtbException("Url by key '{$key}' does not found");
        }
        return $this->externalUrls[$key];
    }

    protected function urlForMainSite($uri)
    {
        return 'http://' . $this->getExternalUrl('main_site') . '/' . $uri;
    }

    protected function urlForUserVoice($uri, $prefix = 'knowledgebase/articles/')
    {
        return 'http://' . $this->getExternalUrl('user_voice') . '/' . $prefix . $uri;
    }

    protected function getPlaceHoldersForBlock($block)
    {
        $return = array();
        foreach ($block as $key => $val) {
            $return['%' . strtoupper($key) . '%'] = $val;
        }
        if (!empty($block['arf_balance'])) {
            $return['%CASH_DIFF%'] = $block['arf_balance'] - $block['tr_balance'];
        }
        return $return;
    }

    protected function getPlaceHoldersForTitles()
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
        $placeHolders = $this->getPlaceHoldersForTitles();

        if (count($this->model->getBlocks())) {
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
            } else/*if (AtbType::CASH == $this->model->getType())*/ {
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
        } elseif ($this->model->getScoreCurrent() > $this->model->getScoreTarget()) {
            return $this->trans(
                "reached-score-title-message",
                $placeHolders
            );
        } elseif (AtbType::CASH == $this->model->getType()) {
            return $this->trans(
                "cash-not-reach-title-message-%CASH%",
                $placeHolders
            );
        } elseif (AtbType::SEARCH == $this->model->getType()) {
            return $this->trans(
                "search-not-reach-title-message",
                $placeHolders
            );
        } else/*if (AtbType::SCORE == $this->model->getType())*/ {
            return $this->trans(
                "score-not-reach-title-message",
                $placeHolders
            );
        }
        return '';
    }

    protected function getTitleMessage()
    {
        $placeHolders = $this->getPlaceHoldersForTitles();
        if (count($this->model->getBlocks())) {
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
            } else/*if (AtbType::CASH == $this->model->getType())*/ {
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
