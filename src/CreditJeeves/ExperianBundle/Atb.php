<?php
namespace CreditJeeves\ExperianBundle;

use Fp\BadaBoomBundle\Bridge\UniversalErrorCatcher\ExceptionCatcher;
use \SoapClient;
use \DOMDocument;
use \Exception;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\ExperianBundle\Exception\Atb as AtbException;
use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\ArfBundle\Map\ArfReport;
use CreditJeeves\ArfBundle\Map\ArfDirectCheck;

/**
 * ATB
 *
 * This class handles the getting the  Profile Information through ATB.
 * We will be using the ARF data which we have got from NetConnect API
 * Calling the ATB by using Soap with required parameters
 * Parsing the response to get required information
 *
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 *
 * @DI\Service("experian.atb")
 */
class Atb
{
    /**
     * @var ArfParser
     */
    private $arfParser;

    /**
     * @var array
     */
    private $directCheck = null;

    /**
     * @var \CreditJeeves\ArfBundle\Map\ArfReport
     */
    private $arfReport;

    /**
     * Name normalizer
     *
     * @var array
     */
    private $dataNames = array(
        'transaction_signature' => 'transaction_signature',
        'score_init' => 'scoreInit',
        'score_best' => 'scoreBest',
        'cash_used' => 'cashUsed',
        'sim_type' => 'simType',
        'message' => 'Message',
    );

    private $blokNames = array(
        'tr_rev_install',
        'tr_amount1',
        'tr_amount1_qual',
        'tr_amount2',
        'tr_amount2_qual',
        'tr_balance',
        'tr_acctnum',
        'tr_subname',
        'tr_subcode',
        'subscriber_phone_number',
    );

    private $requestParamas = array(
        'data' => null, // Required
        'transaction_signature' => '', // Required if exist
        'datasource_name' => "ExpConsUSV7",
        'system_name' => "CJ_Scorex_Plus:Scorex_Plus:Scorex_Plus",
    );

    /**
     * @var SoapClient
     */
    private $client;

    private $configs = array();

    /**
     * @var ExceptionCatcher
     */
    private $catcher;

    /**
     * @DI\InjectParams({
     *     "configs" = @DI\Inject("%experian.atb%"),
     *     "catcher" = @DI\Inject("fp_badaboom.exception_catcher")
     * })
     */
    public function __construct(array $configs, ExceptionCatcher $catcher = null)
    {
        $this->configs = $configs;
        $this->catcher = $catcher;
        $this->client = new SoapClient($this->configs['wsdl_url'], array('trace' => 1));
        $this->client->__setLocation($this->configs['location']);
    }

    /**
     * @return \CreditJeeves\ArfBundle\Map\ArfDirectCheck
     */
    protected function getArfReport()
    {
        if (null == $this->arfReport) {
            $arfArray = $this->arfParser->getArfArray();
            $this->arfReport = new ArfDirectCheck($arfArray);
        }

        return $this->arfReport;
    }

    /**
     * Passes the ARF data and score to ATB to increase the score of a particular user
     *
     * @param ArfParser $arfParser
     * @param float $score
     * @param string $signature
     *
     * @throws AtbException
     *
     * @return
     */
    public function increaseScoreByX(ArfParser $arfParser, $score, $signature)
    {
        $this->arfParser = $arfParser;
        $request = $this->requestParamas;
        $request['data'] = $this->arfParser->getMinimizedArfString();
        $request['score_xpoint'] = $score;
        $request['transaction_signature'] = $signature;
        return $this->getAllBlocks($this->parseATBResponse($this->client->performIncreaseScoreByX_full($request)));
    }

    /**
     * Passes the ARF data and max cash to ATB
     *
     * @param ArfParser $arfParser
     * @param int $maxCash
     * @param string $signature
     *
     * @throws AtbException
     *
     * @return array
     */
    public function bestUseOfCash(ArfParser $arfParser, $maxCash, $signature)
    {
        $this->arfParser = $arfParser;
        $request = $this->requestParamas;
        $request['data'] = $this->arfParser->getMinimizedArfString();
        $request['max_cash'] = $maxCash;
        $request['transaction_signature'] = $signature;
        return $this->getAllBlocks($this->parseATBResponse($this->client->performBestUseOfCash_full($request)));

    }

    public function getConfig($key)
    {
        return $this->configs[$key];
    }

    /**
     * This function passes the ARF data to ATB to get the Raw Data
     *
     * @param ArfParser $arfParser
     * @param string $blockEleName
     *
     * @return array
     */
    public function parseATBResponse($result)
    {
        if (!empty($this->configs['logging'])) {
            file_put_contents(
                $this->configs['logs_dir'] . '/experian/ATB-Soap.xml',
                $this->client->__getLastRequest()
            );
            file_put_contents(
                $this->configs['logs_dir'] . '/experian/ATB-Soap-Response.xml',
                $this->client->__getLastResponse()
            );
        }

        if (0 != $result->code) {
            throw new AtbException($result->message, E_ERROR);
        }

        if (!empty($this->configs['logging'])) {
            file_put_contents(
                $this->configs['logs_dir'] . '/experian/ATB_response.xml',
                $result->result
            );
        }

        $resultArr = array(
            'score_best' => 0,
            'score_init' => 0,
        );
        $resultArr['blocks'] = array();
        $doc = new DOMDocument();
        $doc->loadXML($result->result);

        foreach ($this->dataNames as $key => $name) {
            if (0 != $doc->getElementsByTagName($name)->length) {
                $resultArr[$key] = $doc->getElementsByTagName($name)->item(0)->nodeValue;
            }
        }

        if ($resultArr['score_best'] < $resultArr['score_init']) { // If it is true then something wrong
            return $resultArr;
        }

        $datachangeSet = $doc->getElementsByTagName("DataChanged");
        foreach ($datachangeSet as $datachange) {
            $dataBlockSet = $datachange->getElementsByTagName("S357"); // Exist more
            foreach ($dataBlockSet as $dataBlock) {
                foreach ($dataBlock->childNodes as $tag) {
                    if ('DOMElement' != get_class($tag)) {
                        continue;
                    }
                    $blockArr = array();
                    foreach ($this->blokNames as $name) {
                        if (0 != $tag->getElementsByTagName($name)->length) {
                            $blockArr[$name] = $tag->getElementsByTagName($name)->item(0)->nodeValue;
                        } else {
                            $blockArr[$name] = '';
                        }
                    }
//                    $blockArr['tr_acctnum'] = ArfParser::cropCard($blockArr['tr_acctnum']);
                    $resultArr['blocks'][] = $blockArr;
                }
            }
        }
        return $resultArr;
    }

    /**
     * @param string $cardNumber
     *
     * @return string
     */
    protected function cropCardNumber($cardNumber)
    {
        return $cardNumber;
    }

    /**
     * @return array
     */
    protected function getTradeLines()
    {
        return $this->getArfReport()->getValue(ArfParser::SEGMENT_TRADELINE) ? : array();
    }

    /**
     * @return array
     */
    protected function getDirectCheck()
    {
        if (null === $this->directCheck) {
            $this->directCheck = $this->getArfReport()->getDirectCheck()?:array();
        }
        return $this->directCheck;
    }

    /**
     * @param array $resultArr
     *
     * @return array
     */
    protected function getAllBlocks(array $resultArr)
    {
        $resultArr = $this->composeBlocks($resultArr);
        return $resultArr;
    }

    protected function handleException(Exception $e)
    {
        if ($this->catcher) {
            $this->catcher->handleException($e);
        } else {
            throw $e;
        }
    }

    /**
     * @param array $block
     */
    protected function getTradeLineByBlock(array $block)
    {
        $return = array();
        $directCheck = $this->getDirectCheck();
        foreach ($this->getTradeLines() as $tradeLine) {
            if ($block['tr_subcode'] == $tradeLine['tr_subcode'] &&
                $block['tr_acctnum'] == $tradeLine['tr_acctnum']
            ) {
                if (empty($return)) {
                    $return = $tradeLine;
                    $phone = '';
                    if (!empty($directCheck[$tradeLine['tr_subcode']])) {
                        $phone = $directCheck[$tradeLine['tr_subcode']]['subscriber_phone_number'];
                    }
                    $return['subscriber_phone_number'] = $phone;
                } else {
                    $this->handleException(
                        new AtbException(
                            sprintf(
                                "Found duplicates of block", // TODO return back %s \n in trade lines: %s
                                print_r($block, true),
                                print_r($this->getTradeLines(), true)
                            )
                        )
                    );
                }
            }
        }

        return $return;
    }

    /**
     * @param $block
     *
     * @return Atb
     */
    public function composeBlocks($resultArr)
    {
        $return = array();
        $new = array('banks' => array(), 'tr_balance' => 0);
        $i = 0;
        $positionShift = -10000;
        $positions = array(
            90 => 90 * $positionShift,
            60 => 60 * $positionShift,
            30 => 30 * $positionShift,
        );
        foreach ($resultArr['blocks'] as $block) {
            if (!isset($block['tr_acctnum'])) {
                throw new AtbException('"tr_acctnum" does not exist!!!');
            }

            $tradeLine = $this->getTradeLineByBlock($block);
            switch ((int)($resultArr['sim_type'] / 10)) {
                case 10:
                    if ($block['tr_balance'] < $tradeLine['tr_balance']) { // TODO Maybe better to use !=
                        $block['arf_balance'] = $tradeLine['tr_balance'];
                        $block['subscriber_phone_number'] = $tradeLine['subscriber_phone_number']?:'';
                        $block['tr_acctnum'] = $this->cropCardNumber($block['tr_acctnum']);
                    } else {
                        $this->handleException(
                            new AtbException(
                                sprintf(
                                    "Data was missed", // TODO return back from: \nBlock: %s \nTrade lines: %s
                                    print_r($block, true),
                                    print_r($this->getTradeLines(), true)
                                )
                            )
                        );
                        continue 2;
                    }
                    break;
                case 20:
                    // TODO add checks
                    $block['tr_acctnum'] = $this->cropCardNumber($block['tr_acctnum']);
                    break;
                case 30:
                case 40:
                    if (!empty($tradeLine) &&
                        $block['tr_balance'] < $tradeLine['tr_balance'] &&
                        0 < $block['tr_balance']
                    ) {
                        $block['arf_balance'] = $tradeLine['tr_balance'];
                        $block['tr_acctnum'] = $this->cropCardNumber($block['tr_acctnum']);
                        break;
                    } elseif ('NEW ACCOUNT' == $block['tr_subname']) {
                        $new['tr_balance'] = $block['tr_balance'];
                    } else {
                        $new['banks'][] = $block['tr_subname'];
                    }
                    continue 2;
                default:
                    $this->handleException(
                        new AtbException(
                            sprintf(
                                "Unsupported type '%s'", // TODO return back \nBlocks of ATB: %s
                                $resultArr['sim_type'],
                                print_r($resultArr, true)
                            )
                        )
                    );
                    continue 2;
            }

            if ($days = $this->isLatePayment($tradeLine)) {
                $newPosition = $positions[$days];
                if (!empty($block['tr_balance'])) {
                    foreach ($return as $position => $val) {
                        if (!empty($val['tr_balance']) &&
                            $days * $positionShift >= $position &&
                            $position > ($days + 30) * $positionShift
                        ) {
                            if ($val['tr_balance'] >= $block['tr_balance']) {
                                $decrease = 100;
                                if ($val['tr_balance'] == $block['tr_balance']) {
                                    $decrease = 1;
                                }
                                $newPosition = $position + $decrease;
                            }
                        }
                    }
                }
                $return[$newPosition] = $block;
                $positions[$days] -= 1000;
            } else {
                $return[$i++] = $block;
            }
        }
        ksort($return);
        if (!empty($new['tr_balance']) || !empty($new['banks'])) {
            if (!empty($new['tr_balance']) && !empty($new['banks'])) {
                $new['banks'] = implode(', ', $new['banks']);
                $return[] = $new;
            } else {
                $this->handleException(
                    new AtbException(
                        sprintf(
                            "Data was missed from new account", // TODO return back: %s\nBlocks of ATB:\nTrade lines: %s
                            print_r($resultArr, true),
                            print_r($this->getTradeLines(), true)
                        )
                    )
                );
            }
        }
        $resultArr['blocks'] = array_values($return);
        return $resultArr;
    }

    /**
     * @param $tradeLine
     *
     * @return bool|int
     */
    private function isLatePayment($tradeLine)
    {
        $days = false;
        if (is_array($tradeLine) && !empty($tradeLine['tr_status'])) {
            if (80 <= $tradeLine['tr_status'] && 84 >= $tradeLine['tr_status']) {
                $days = 90;
            } elseif (78 <= $tradeLine['tr_status'] && 79 >= $tradeLine['tr_status']) {
                $days = 60;
            } elseif (71 <= $tradeLine['tr_status'] && 77 >= $tradeLine['tr_status']) {
                $days = 30;
            }
        }
        return $days;
    }
}
