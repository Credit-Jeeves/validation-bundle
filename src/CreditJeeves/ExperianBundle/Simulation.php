<?php
namespace CreditJeeves\ExperianBundle;

use CreditJeeves\DataBundle\Entity\Atb as AtbEntity;

class Simulation
{

    protected $type = '';
    protected $input = 0;
    protected $targetScoreSearchResponse = false;
    protected $targetScoreSearch = false;
    protected $scoreCurrent = 0;
    protected $scoreTarget = 0;
    protected $tierBest = 0;

    protected $score_init = 0;
    protected $score_best = 0;
    protected $cash_used = 0;
    protected $sim_type = 0;
    protected $message = '';

    /**
     * @var Atb
     */
    protected $atb;

    public function __construct(Atb $atb)
    {
        $this->atb = $atb;
    }

    public function getResultData($full = false)
    {
        $return = $this->getResult();
        $return['type'] = $this->getType();
        $return['input'] = $this->getInput();
        if (!$full) {
            unset($return['transaction_signature']);
        }

        $return['tierBest']= cjApplicantScore::getTier(empty($return['score_best'])?0:$return['score_best']);

        if (sfConfig::get('experian_atb_skip_sim_type_400') && 400 == $return['sim_type']) {
            $return['blocks'] = array();
            $return['score_best'] = 0;
            $return['use_cash'] = sfConfig::get('experian_atb_default_cash');
            return $return;
        }

        if (!empty($return['blocks'])) {
            foreach ($return['blocks'] as &$tLine) {
                if (empty($tLine['tr_acctnum'])) continue;
                $tLine['tr_acctnum'] = cjArfReport::hideAccount($tLine['tr_acctnum']);
            }
        }

        return $return;
    }

    protected function getFico()
    {
        return getFicoScore($this->score_best);
    }

    protected function getScoreBest()
    {
        if (!empty($this->isDealerSide)) {
    //      return i18n.__('tier-%TIER%', {TIER: this.tierBest().value}, 'simulation');
            return $this->tierBest;
        } else {
            return $this->score_best;
        }
    }

    protected function getSimTypeGroup() {
        return $this->sim_type / 10;
    }

    /**
     *
     * @param data
     *
     * @return {Boolean}
     */
    protected function fill($data, $isResult = false)
    {
//        if ($data instanceof) {
//            throw new Error("No data passed");
//        }
        if ($data['use_cash']) {
            $this->bestUseOfCash(data.use_cash); //TODO move to config file
            return false;
        }

        if (!$isResult && $data['score_best'] < $this->scoreTarget()) {
            if (false == $this->targetScoreSearchResponse() &&
                true == $this->targetScoreSearch() &&
                data.blocks.length
            ) {
                $this->targetScoreSearchResponse(true);
                $this->increaseScoreByX(parseInt(data.score_best) - parseInt($this->scoreCurrent()));
                return false;
            } else if (false == $this->targetScoreSearch() &&
                'score' == data.type &&
                !data.blocks.length &&
                ($this->scoreCurrent() + 1) != $this->scoreTarget()
            ) {
                $this->targetScoreSearch(true);
                $this->increaseScoreByX(1, false);
                return false;
            }
        }

        jQuery.each(data, function(index, value) {
                if (self[index]) {
                    self[index](value);
          } else {
                    console.warn('Index "' + index + '" does not exist');
                }
        });

        if ($this->message()) { // Dev message
            console.info($this->message());
        }
        return true;
    }

//var sendData = function(data) {
//    var form = jQuery('#' + _formId);
//    jQuery.ajax({
//      url: Routing.generate('ajax_simulation'),
//      type: 'POST',
//      timeout: 30000, // 30 secs
//      dataType: 'json',
//      data: data,
//      beforeSend: function(jqXHR, settings) {
//        _forms.trigger('sfJqueryFormValidation.onBeforeSend', [form, jqXHR, settings]);
//    },
//      error: function(jqXHR, errorThrown, textStatus) {
//        _forms.trigger('sfJqueryFormValidation.onError', [form, jqXHR, errorThrown, textStatus]);
//    },
//      success: function(data, textStatus, jqXHR) {
//        _forms.trigger('sfJqueryFormValidation.onSuccess', [form, data, textStatus, jqXHR]);
//    },
//      complete: function(jqXHR, textStatus) {
//        _forms.trigger('sfJqueryFormValidation.onComplete', [form, jqXHR, textStatus]);
//    }
//    });
//  }
//
//    protected bestUseOfCash = function(cash, toSave) {
//    var formData = jQuery('#' + _formId).serializeArray();
//    if ('simulator[best_use_of_cash]' != formData[0].name) {
//        throw new Error('Form was changed, need corrections');
//    }
//    formData[0].value = cash;
//
//    if (typeof(toSave) == "undefined") {
//        toSave = true;
//    }
//    if (toSave) {
//        formData.push({
//        name: 'save',
//        value: 1
//      });
//    }
//    var data = jQuery.param(formData, false);
//    sendData(data, toSave);
//    return false;
//  };
//
//    protected increaseScoreByX = function(score, toSave) {
//    var data = {score: score};
//    if (typeof(toSave) == "undefined") {
//        toSave = true;
//    }
//    if (toSave) {
//        data.save = 1;
//    }
//    sendData(data);
//  };



    protected function getPlaceHolders()
    {
        return array(
            '%STEPS%' => count($this->getAllBlocks()),
            '%POINTS_INCREASE%' => $this->score_best - $this->scoreCurrent,
            '%CASH_USED%' => $this->cash_used,
            '%SCORE_BEST%' => $this->score_best,
            '%CASH%' => $this->input,
            '%TIER%' => $this->tierBest
        );
      }

    public function getTitle()
    {
        $placeHolders = $this->getPlaceHolders();

        if ($this->getAllBlocks()) {
            if ('score' == $this->type()) {
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
            } else if ('cash' == $this->type()) {
                if ($this->scoreTarget() < $this->score_best()) {
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
        } else if ('cash' == $this->type()) {
            return $this->trans(
                "cash-not-reach-title-message-%CASH%",
                placeHolders,
                'simulation'
            );
        } else if ($this->scoreCurrent() > $this->scoreTarget()) {
            return $this->trans(
                "reached-score-title-message",
                placeHolders,
                'simulation'
            );
        } else if ('score' == $this->type()) {
            return $this->trans(
                "score-not-reach-title-message",
                placeHolders,
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
                if ($this->scoreTarget() < $this->score_best()) {
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
