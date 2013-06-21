<?php
namespace CreditJeeves\ExperianBundle\Model;

use CreditJeeves\ExperianBundle\Model as Base;

/**
 * Atb data model
 *
 * @author Ton Sharp <66ton99@gmail.com>
 */
class Atb
{
    /**
     * @var bool
     */
    protected $isDealerSide = false;

    /**
     * @var \CreditJeeves\DataBundle\Enum\AtbType
     */
    protected $type;

    /**
     * @var int
     */
    protected $input;

    /**
     * @var int
     */
    protected $scoreInit = 0;

    /**
     * @var int
     */
    protected $scoreBest = 0;

    /**
     * @var int
     */
    protected $scoreCurrent = 0;

    /**
     * @var int
     */
    protected $scoreTarget = 0;

    /**
     * @var int
     */
    protected $cashUsed = 0;

    /**
     * @var int
     */
    protected $simType;

    /**
     * @var string
     */
    protected $simTypeGroup;

    /**
     * @var string
     */
    protected $simTypeMessage;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var array
     */
    protected $blocks = array();

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $titleMessage;

    public function setIsDealerSide($isDealerSide)
    {
        $this->isDealerSide = $isDealerSide;
        return $this;
    }

    public function getIsDealerSide()
    {
        return $this->isDealerSide;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function setScoreInit($scoreInit)
    {
        $this->scoreInit = $scoreInit;
        return $this;
    }

    public function getScoreInit()
    {
        return $this->scoreInit;
    }

    public function setScoreBest($scoreBest)
    {
        $this->scoreBest = $scoreBest;
        return $this;
    }

    public function getScoreBest()
    {
        return $this->scoreBest;
    }

    public function setScoreTarget($scoreTarget)
    {
        $this->scoreTarget = $scoreTarget;
        return $this;
    }

    public function getScoreTarget()
    {
        return $this->scoreTarget;
    }

    public function setCashUsed($cashUsed)
    {
        $this->cashUsed = $cashUsed;
        return $this;
    }

    public function getCashUsed()
    {
        return $this->cashUsed;
    }

    public function setSimType($simType)
    {
        $this->simType = $simType;
        return $this;
    }

    public function getSimType()
    {
        return $this->simType;
    }

    public function setSimTypeGroup($simTypeGroup)
    {
        $this->simTypeGroup = $simTypeGroup;
        return $this;
    }

    public function getSimTypeGroup()
    {
        return $this->simTypeGroup;
    }

    public function setSimTypeMessage($simTypeMessage)
    {
        $this->simTypeMessage = $simTypeMessage;
        return $this;
    }

    public function getSimTypeMessage()
    {
        return $this->simTypeMessage;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setScoreCurrent($scoreCurrent)
    {
        $this->scoreCurrent = $scoreCurrent;
        return $this;
    }

    public function getScoreCurrent()
    {
        return $this->scoreCurrent;
    }

    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
        return $this;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitleMessage($titleMessage)
    {
        $this->titleMessage = $titleMessage;
        return $this;
    }

    public function getTitleMessage()
    {
        return $this->titleMessage;
    }
}
