<?php
namespace CreditJeeves\ExperianBundle\Model;

use  CreditJeeves\ExperianBundle\Model as Base;

class Atb extends Base
{
    protected $type;
    protected $input;
    protected $scoreInit = 0;
    protected $scoreBest = 0;
    protected $cashUsed = 0;
    protected $simType = 0;
    protected $message = '';
    protected $scoreCurrent = 0;
    protected $scoreTarget = 0;
    protected $blocks = array();

    protected $title;
    protected $titleMessage;

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
        return $this->setSimType;
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
