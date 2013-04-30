<?php
namespace CreditJeeves\ExperianBundle;

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
        EntityManager $em,
        AtbRepository $entityRepo
    ) {
        $this->atb = $atb;
        $this->converter = $converter;
        $this->em = $em;
        $this->entityRepo = $entityRepo;
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

    /**
     * @return AtbRepository
     */
    public function getEntityRepo()
    {
        return $this->entityRepo;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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

        switch($type) {
            case AtbType::CASH:
                $result = $this->getAtb()->bestUseOfCash($arfParser, $input);
                break;
            case AtbType::SCORE:
                $result = $this->getAtb()->increaseScoreByX($arfParser, $input);
                break;
            default:
                throw new AtbException("Wrong type '{$type}'");
        }

        $entity = new Entity();
        $entity->setReport($report);
        $entity->setType($type);
        $entity->setInput($input);
        $entity->setResult($result);
        $this->getEM()->persist($entity);
        $this->getEM()->flush(); // TODO move it, if it would be required

        $model = $this->getConverter()->getModel($entity, $arfParser, $targetScore);

        return $model;
    }
}
