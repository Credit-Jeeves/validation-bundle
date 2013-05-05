<?php
namespace CreditJeeves\ExperianBundle;

use CreditJeeves\CoreBundle\Arf\ArfParser;
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

    protected function increaseScoreByX($targetScore, $scoreCurrent)
    {
        $signature = $this->findLastTransactionSignature(AtbType::SCORE);
        $parser = $this->getReport()->getArfParser();

        $input = $targetScore - $scoreCurrent;
        $result = $this->getAtb()->increaseScoreByX($parser, $input, $signature);

        return $result;
    }

    /**
     * @param \CreditJeeves\DataBundle\Enum\AtbType $type
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
        $lastSimulationEntity = $this->findLastSimulationEntity(
            AtbType::CASH == $type ? null : $targetScore,
            array($type),
            $input
        );
        if ($lastSimulationEntity) {
            return $this->getConverter()->getModel($lastSimulationEntity);
        }

        $scoreCurrent = $report->getArfReport()->getValue(ArfParser::SEGMENT_RISK_MODEL, ArfParser::REPORT_SCORE);

        switch($type) {
            case AtbType::CASH:
                $result = $this->getAtb()->bestUseOfCash(
                    $arfParser,
                    $input,
                    $this->findLastTransactionSignature($type)
                );
                break;
            case AtbType::SCORE:
                $result = $this->increaseScoreByX($targetScore, $scoreCurrent);
                break;
            default:
                throw new AtbException("Wrong type '{$type}'");
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
