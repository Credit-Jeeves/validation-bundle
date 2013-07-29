<?php
namespace CreditJeeves\ExperianBundle;

use CreditJeeves\ArfBundle\Parser\ArfParser;
use CreditJeeves\DataBundle\Entity\AtbRepository;
use CreditJeeves\DataBundle\Enum\AtbType;
use CreditJeeves\DataBundle\Entity\Atb as Entity;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Model\User;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use CreditJeeves\ExperianBundle\Converter\Atb as Converter;
use CreditJeeves\ExperianBundle\Model\Atb as Model;
use CreditJeeves\ExperianBundle\Atb;
use CreditJeeves\ExperianBundle\Exception\Atb as AtbException;

/**
 * Facade - design pattern
 *
 * @DI\Service("experian.atb_simulation")
 */
class AtbSimulation
{
    const SEARCH_CASH = 10000;

    /**
     * @var ReportPrequal
     */
    protected $report;

    /**
     * @var Atb
     */
    protected $atb;

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var AtbRepository
     */
    protected $entityRepo;

    /**
     * @var User
     */
    protected $user;

    /**
     * @DI\InjectParams({
     *     "atb" = @DI\Inject("experian.atb"),
     *     "converter" = @DI\Inject("experian.atb.converter"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "entityRepo" = @DI\Inject("data.entity.repository")
     * })
     */
    public function __construct(
        Atb $atb,
        Converter $converter,
        EntityManager $em
    ) {
        $this->atb = $atb;
        $this->converter = $converter;
        $this->em = $em;
    }

    /**
     * @return ReportPrequal
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @return Atb
     */
    public function getAtb()
    {
        return $this->atb;
    }

    /**
     * @return Converter
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * @return EntityManager
     */
    public function getEM()
    {
        return $this->em;
    }

    protected function findLastTransactionSignature($type)
    {
        $lastSimulationEntity = $this->findLastSimulationEntity(null, array($type));
        $signature = '';

        if ($lastSimulationEntity) {
            $signature = $lastSimulationEntity->getTransactionSignature();
        }
        return $signature;
    }

    /**
     * @param int $targetScore
     *
     * @return array | false
     */
    protected function cashBinarySearch($targetScore)
    {
        $signature = $this->findLastTransactionSignature(AtbType::CASH);

        $parser = $this->getReport()->getArfParser();

        $input = static::SEARCH_CASH;

        $previousInput = null;
        $bestInput = null;

        $bestResult = false;

        $atb = $this->getAtb();

        while (true) {
            $tmpResult = $atb->bestUseOfCash($parser, $input, $signature);
            if (empty($tmpResult)) {
                throw new AtbException("Empty result of bestUseOfCash with input '{$input}'");
            }
            if (!$bestResult && $targetScore > $tmpResult['score_best']) {
                return false;
            }

            if ($targetScore <= $tmpResult['score_best']) {
                $bestResult = $tmpResult;
                $bestInput = $input;
                $input = (int)($input / 2);
            } elseif (100 < ($bestInput - $input) / 2) { // TODO improve logic
                $input = $bestInput - (int)( ($bestInput - $input) / 2 );
            } else {
                return $bestResult;
            }
        }
    }

    protected function increaseScoreByX($targetScore, $scoreCurrent)
    {
        $signature = $this->findLastTransactionSignature(AtbType::SCORE);
        $parser = $this->getReport()->getArfParser();
        $atb = $this->getAtb();

        $input = $targetScore - $scoreCurrent;
        $result = $atb->increaseScoreByX($parser, $input, $signature);

        if (400 == $result['sim_type']) {
            $mortgage = $this->getReport()->getArfReport()->getValue(
                ArfParser::SEGMENT_PROFILE_SUMMARY,
                ArfParser::REPORT_BALANCE_REAL_ESTATE
            );
            if (empty($mortgage)) {
                if ($result = $this->cashBinarySearch($targetScore)) {
                    $result['type'] = AtbType::SEARCH;
                    return $result;
                }
            }
        }

        if (empty($result['blocks'])) {
            $result = $atb->increaseScoreByX($parser, 1, $signature);
            if (!empty($result['blocks']) && !empty($result['score_best'])) {
                $result = $atb->increaseScoreByX($parser, $result['score_best'] - $scoreCurrent, $signature);
            }
        }

        if (400 == $result['sim_type'] && empty($mortgage) && empty($result['blocks'])) {
            $result['type'] = AtbType::SEARCH;
        }

        return $result;
    }

    /**
     * @param AtbType $type
     * @param int $input
     * @param ReportPrequal $report
     * @param bool $isSave
     *
     * @return Model
     */
    public function simulate($type, $input, ReportPrequal $report, $targetScore)
    {
        $this->report = $report;
        $arfParser = $report->getArfParser();
        $scoreCurrent = $report->getArfReport()->getValue(ArfParser::SEGMENT_RISK_MODEL, ArfParser::REPORT_SCORE);
        if (AtbType::CASH == $type || $scoreCurrent < $targetScore) {
            $lastSimulationEntity = $this->findLastSimulationEntity(
                AtbType::CASH == $type ? null : $targetScore,
                array($type),
                $input
            );
            if ($lastSimulationEntity) {
                return $this->getConverter()->getModel($lastSimulationEntity);
            }

            switch($type) {
                case AtbType::CASH:
                    $result = $this->getAtb()->bestUseOfCash(
                        $arfParser,
                        $input,
                        $this->findLastTransactionSignature($type)
                    );
                    break;
                case AtbType::SCORE:
                    $input = $targetScore - $scoreCurrent;
                    $result = $this->increaseScoreByX($targetScore, $scoreCurrent);
                    if (!empty($result['type'])) {
                        $type = $result['type'];
                        unset($result['type']);
                    }
                    break;
                default:
                    throw new AtbException("Wrong type '{$type}'");
            }
        } else {
            $input = $targetScore - $scoreCurrent;
            $result = array(
                'sim_type' => 0,
                'transaction_signature' => '',
                'blocks' => array(),
            );
        }

        $entity = new Entity();
        $entity->setReport($report);
        $entity->setType($type);
        $entity->setInput($input);
        $entity->setResult($result);
        $entity->setScoreCurrent($scoreCurrent);
        $entity->setScoreTarget($targetScore);

        $this->getEM()->persist($entity);
        $this->getEM()->flush(); // TODO move it, if it would be required

        $model = $this->getConverter()->getModel($entity);

        return $model;
    }

    /**
     * @param ReportPrequal $report
     * @param $targetScore
     * @param null $type
     * @param null $input
     *
     * @return Entity | null
     */
    protected function findLastSimulationEntity(
        $targetScore,
        array $type = array(AtbType::SCORE, AtbType::SEARCH),
        $input = null
    ) {
        $entity = $this->getEM()
            ->getRepository('DataBundle:Atb')
            ->findLatsSimulationEntity($this->getReport()->getId(), $targetScore, $type, $input);

        return $entity;
    }

    public function getLastSimulation(ReportPrequal $report, $targetScore)
    {
        $this->report = $report;
        if (!($entity = $this->findLastSimulationEntity($targetScore))) {
            return null;
        }
        return $this->getConverter()->getModel($entity);
    }
}
