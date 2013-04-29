<?php
namespace CreditJeeves\ExperianBundle;

use CreditJeeves\CoreBundle\Arf\ArfReport;
use CreditJeeves\DataBundle\Entity\Atb as AtbEntity;
use CreditJeeves\DataBundle\Entity\AtbRepository;
use CreditJeeves\DataBundle\Entity\ReportPrequal;
use CreditJeeves\DataBundle\Enum\AtbType;
use CreditJeeves\DataBundle\Model\User;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * @DI\Service("experian.simulation")
 */
class SimulationOld
{
    protected $isDealerSide = false;

    protected $blocks = array();
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

    /**
     * @var User
     */
    protected $user;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @DI\InjectParams({
     *     "atb" = @DI\Inject("experian.atb"),
     *     "em" = @DI\Inject("doctrine.orm.default_entity_manager"),
     *     "translator" = @DI\Inject("translator.default")
     * })
     */
    public function __construct(Atb $atb, $em, $translator)
    {
        $this->atb = $atb;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function getResultData($result, $full = false)
    {
        $result['type'] = $this->getType();
        $result['input'] = $this->getInput();
        if (!$full) {
            unset($result['transaction_signature']);
        }

//        $return['tierBest']= cjApplicantScore::getTier(empty($return['score_best'])?0:$return['score_best']);

        if ($this->atb->getConfig('skip_sim_type_400') && 400 == $result['sim_type']) {
            $result['blocks'] = array();
            $result['score_best'] = 0;
            $result['use_cash'] = $this->atb->getConfig('default_cash');
            return $result;
        }

        if (!empty($result['blocks'])) {
            foreach ($result['blocks'] as &$tLine) {
                if (empty($tLine['tr_acctnum'])) {
                    continue;
                }
                $tLine['tr_acctnum'] = ArfReport::hideAccount($tLine['tr_acctnum']);
            }
        }

        return $result;
    }

    protected function getSimTypeGroup()
    {
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
            $this->bestUseOfCash($this->getArfParser(), $data['use_cash']); //TODO move to config file
            return false;
        }

        if (!$isResult && $data['score_best'] < $this->scoreTarget) {
            if (false == $this->targetScoreSearchResponse &&
                true == $this->targetScoreSearch &&
                count($data['blocks'])
            ) {
                $this->targetScoreSearchResponse = true;
                $this->increaseScoreByX($this->user, $data['score_best'] - $this->scoreCurrent);
                return false;
            } elseif (false == $this->targetScoreSearch &&
                'score' == $data['type'] &&
                !count($data['blocks']) &&
                ($this->scoreCurrent + 1) != $this->scoreTarget
            ) {
                $this->targetScoreSearch = true;
                $this->increaseScoreByX($this->user, 1, false);
                return false;
            }
        }

//        foreach ($data as $index => $value) {
//            if (self[index]) {
//                self[index](value);
//            } else {
//                console.warn('Index "' + index + '" does not exist');
//            }
//        }
//
//        if ($this->message) { // Dev message
//            console.info($this->message());
//        }
        return true;
    }

    /**
     * @return AtbEntity
     */
    protected function createEntity()
    {
        $entity = new AtbEntity();
        $entity->setUser($this->user);
        return $entity;
    }

    protected function saveEntity($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    protected function getArfParser()
    {
        /** @var $report ReportPrequal */
        $report = $this->user->getReportsPrequal()->last();
        return is_object($report) ? $report->getArfParser() : null;
    }

    protected function trans($string, $parameters = array())
    {
        return $this->translator->trans($string, $parameters, 'simulation');
    }

    public function bestUseOfCash(User $user, $cash, $isSave = true)
    {
        $this->user = $user;
        $result = $this->atb->bestUseOfCash($this->getArfParser(), $cash);

        if ($isSave) {
            $entity = $this->createEntity();
            $entity->setType(AtbType::CASH);
            $entity->setInput($cash);
            $entity->setResult($result);
            $this->saveEntity($entity);
        }

        return $result;

    }

    protected function getAllBlocks()
    {
        return $this->blocks;
    }

    public function increaseScoreByX(User $user, $score, $isSave = true)
    {
        $this->user = $user;
        $result = $this->atb->increaseScoreByX($this->getArfParser(), $score);

        if ($isSave) {
            $entity = $this->createEntity();
            $entity->setType(AtbType::SCORE);
            $entity->setInput($score);
            $entity->setResult($result);
            $this->saveEntity($entity);
        }

        return $result;
    }
}
