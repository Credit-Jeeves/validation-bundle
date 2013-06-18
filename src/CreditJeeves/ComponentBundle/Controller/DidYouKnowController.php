<?php
namespace CreditJeeves\ComponentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Entity\Report;
use CreditJeeves\CoreBundle\Arf\ArfParser;
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
                'subheading' => 'Your tax refund can help.',
                'message' => 'A third of Americans use their tax refund to pay down high-interest debt. File your taxes today with H&R Block.',
                'href' => 'http://www.kqzyfj.com/click-6606250-10523214',
                'href_text' => '',
                'img' => 'https://www.lduhtrp.net/image-6606250-10523214',
            )
        );
        $this->nScore = $this->get('core.session.applicant')->getUser()->getScores()->last()->getScore();
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
                'subheading' => 'There may be cash in your home.',
                'message' => 'Mortgage rates are still at historic lows. You may be able to refinance at a lower rate and get cash out to help you with your other debt. Submit a loan request at Lending Tree to see offers from a variety of lenders.',
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
                    'message' => 'It looks like you might not have a credit card in your profile. By opening a new card, you\'ll add to the mix of credit you\'re using which may help your score.',
                    'href' => 'offers/credit-cards-for-good-credit-or-excellent-credit',
                    'href_text' => 'View Credit Cards for Good or Excellent Credit',
                    'img' => false
                );
            } else {
                $this->aOffers[] = array(
                    'subheading' => 'A new credit may help.',
                    'message' => 'It looks like you might not have a credit card in your profile. By opening a new card, you\'ll add to the mix of credit you\'re using which may help your score.',
                        'href' => 'offers/credit-cards-for-bad-credit-or-no-credit',
                        'href_text' => 'View Credit Cards for No Credit or Building Credit',
                        'img' => false,
                );
            }
        } elseif ($nAvailableDebt >= 30) {
            if ($this->nScore >= 700) {
                $this->aOffers[] = array(
                    'subheading' => 'A new credit card can help.',
                    'message' => 'Your credit card utilization is '.$nAvailableDebt.'%. By opening a new card and maintaining a zero balance, you could lower your overall utilization which may help your score.',
                    'href' => 'offers/credit-cards-for-good-credit-or-excellent-credit',
                    'href_text' => 'View Credit Cards for Good or Excellent Credit',
                    'img' => false,
                );
            } else {
                $this->aOffers[] = array(
                    'subheading' => 'A new credit card can help.',
                    'message' => 'Your credit card utilization is '.$nAvailableDebt.'%. By opening a new card and maintaining a zero balance, you could lower your overall utilization which may help your score.',
                    'href' => 'offers/credit-cards-for-bad-credit-or-no-credit',
                    'href_text' => 'View Credit Cards for No Credit or Building Credit',
                    'img' => false,
                );
            }
        } else {
            if ($this->nScore >= 700) {
                    $this->aOffers[] = array(
                        'subheading' => 'A new credit card can help.',
                        'message' => 'If you are responsible with your credit, additional cards may help establish more payment history.',
                        'href' => 'offers/credit-cards-for-good-credit-or-excellent-credit',
                        'href_text' => 'View Credit Cards for Good or Excellent Credit',
                        'img' => false,
                    );
            } else {
                $this->aOffers[] = array(
                    'subheading' => 'A new credit card can help.',
                    'message' => 'If you are responsible with your credit, additional cards may help establish more payment history.',
                    'href' => 'offers/credit-cards-for-bad-credit-or-no-credit',
                    'href_text' => 'View Credit Cards for No Credit or Building Credit',
                    'img' => false,
                );
            }
        }
    }
}
