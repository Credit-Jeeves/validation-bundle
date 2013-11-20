<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DidYouKnowController extends Controller
{
    /**
     * @Template()
     * @param \Report $Report
     * @return array
     */
    public function indexAction(Report $Report)
    {
        $this->aOffers = array(
            $default = array(
                'subheading' => 'dyk.subheading.default',
                'message' => 'dyk.message.default',
                'href' => 'http://www.kqzyfj.com/click-6606250-10523214',
                'href_text' => '',
                'img' => 'https://www.lduhtrp.net/image-6606250-10523214',
            )
        );
        $this->nScore = $this->get('core.session.applicant')->getUser()->getLastScore();
        $this->cjArfReport = $Report->getArfReport();
        $this->addMortgageOffer();
        $this->addCreditCardOffer();
        $index = rand(0, sizeof($this->aOffers) - 1);
        return $this->aOffers[$index];
    }

    private function addMortgageOffer()
    {
        $nMortgageDebt = $this->cjArfReport->getValue(
            ArfParser::SEGMENT_PROFILE_SUMMARY,
            ArfParser::REPORT_BALANCE_REAL_ESTATE
        );
        if ($nMortgageDebt > 5000) {
            $this->aOffers[] = array(
                'subheading' => 'dyk.subheading.cash_in_home',
                'message' => 'dyk.message.mortgage',
                'href' => 'http://www.credit.com/r2/loans/af=p96545&c=271202-607a665380',
                'href_text' => '',
                'img' => 'http://www.credit.com/c/loans/af=p96545&c=271329-785973021d',
            );
        }
    }

    private function addCreditCardOffer()
    {
        $nAvailableDebt = 100 - intval(
            $this->cjArfReport->getValue(
                ArfParser::SEGMENT_PROFILE_SUMMARY,
                ArfParser::REPORT_TOTAL_REVOLVING_AVAILABLE_PERCENT
            )
        );
        if ($nAvailableDebt == 100) {
            if ($this->nScore >= 700) {
                $this->aOffers[] = array(
                    'subheading' => 'A new credit may help.',
                    'message' => 'dyk.message.no_credit',
                    'href' => 'offers/credit-cards-for-good-credit-or-excellent-credit',
                    'href_text' => 'dyk.hreftext.view_good',
                    'img' => false
                );
            } else {
                $this->aOffers[] = array(
                    'subheading' => 'dyk.subheading.new_credit',
                    'message' => 'dyk.message.no_credit',
                    'href' => 'offers/credit-cards-for-bad-credit-or-no-credit',
                    'href_text' => 'dyk.hreftext.view_no',
                    'img' => false,
                );
            }
        } elseif ($nAvailableDebt >= 30) {
            if ($this->nScore >= 700) {
                $this->aOffers[] = array(
                    'subheading' => 'dyk.subheading.new_credit',
                    'message' => $this->get('translator.default')->trans(
                        'dyk.message.utilization',
                        array(
                            '%AVAILABLE_DEBT%' => $nAvailableDebt,
                        )
                    ),
                    'href' => 'offers/credit-cards-for-good-credit-or-excellent-credit',
                    'href_text' => 'dyk.hreftext.view_no',
                    'img' => false,
                );
            } else {
                $this->aOffers[] = array(
                    'subheading' => 'dyk.subheading.new_credit',
                    'message' => $this->get('translator.default')->trans(
                        'dyk.message.utilization',
                        array(
                            '%AVAILABLE_DEBT%' => $nAvailableDebt,
                        )
                    ),
                    'href' => 'offers/credit-cards-for-bad-credit-or-no-credit',
                    'href_text' => 'dyk.hreftext.view_no',
                    'img' => false,
                );
            }
        } else {
            if ($this->nScore >= 700) {
                $this->aOffers[] = array(
                    'subheading' => 'dyk.subheading.new_credit',
                    'message' => 'dyk.message.additional',
                    'href' => 'offers/credit-cards-for-good-credit-or-excellent-credit',
                    'href_text' => 'dyk.hreftext.view_no',
                    'img' => false,
                );
            } else {
                $this->aOffers[] = array(
                    'subheading' => 'dyk.subheading.new_credit',
                    'message' => 'dyk.message.additional',
                    'href' => 'offers/credit-cards-for-bad-credit-or-no-credit',
                    'href_text' => 'dyk.hreftext.view_no',
                    'img' => false,
                );
            }
        }
    }
}
