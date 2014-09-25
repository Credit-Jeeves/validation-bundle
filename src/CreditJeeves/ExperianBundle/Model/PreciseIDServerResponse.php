<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use CreditJeeves\ExperianBundle\Model\Error;

trait PreciseIDServerResponse
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("SessionID")
     * @Serializer\Groups({"CreditJeeves"})
     * @var string
     */
    protected $sessionId;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Messages")
     * @Serializer\SerializedName("Messages")
     * @var Messages
     */
    protected $messages;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Summary")
     * @Serializer\SerializedName("Summary")
     * @Serializer\Groups({"CreditJeeves"})
     * @var Summary
     */
    protected $summary;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBDetail")
     * @Serializer\SerializedName("GLBDetail")
     * @Serializer\Groups({"CreditJeeves"})
     * @var GLBDetail
     */
    protected $GLBDetail;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\Error")
     * @Serializer\SerializedName("Error")
     * @Serializer\Groups({"CreditJeeves"})
     * @var Error
     */
    protected $error;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\KBAScore")
     * @Serializer\SerializedName("KBAScore")
     * @var KBAScore
     */
    protected $kbaScore;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\KBA")
     * @Serializer\SerializedName("KBA")
     * @var KBA
     */
    protected $kba;

    /**
     * @param string $sessionId
     *
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return Messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Messages $messages
     *
     * @return $this
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @param GLBDetail $GLBDetail
     *
     * @return $this
     */
    public function setGLBDetail($GLBDetail)
    {
        $this->GLBDetail = $GLBDetail;

        return $this;
    }

    /**
     * @return GLBDetail
     */
    public function getGLBDetail()
    {
        if (empty($this->GLBDetail)) {
            $this->GLBDetail = new GLBDetail();
        }
        return $this->GLBDetail;
    }

    /**
     * @param Summary $summary
     *
     * @return $this
     */
    public function setSummary(Summary $summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return Summary
     */
    public function getSummary()
    {
        if (null == $this->summary) {
            $this->summary = new Summary();
        }
        return $this->summary;
    }

    /**
     * @param Error $error
     *
     * @return $this
     */
    public function setError(Error $error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return Error
     */
    public function getError()
    {
        if (null == $this->error) {
            $this->error = new Error();
        }
        return $this->error;
    }

    /**
     * @return KBAScore
     */
    public function getKbaScore()
    {
        if (null == $this->kbaScore) {
            $this->kbaScore = new KBAScore();
        }

        return $this->kbaScore;
    }

    /**
     * @param KBAScore $kbaScore
     *
     * @return $this
     */
    public function setKbaScore($kbaScore)
    {
        $this->kbaScore = $kbaScore;

        return $this;
    }

    /**
     * @return KBA
     */
    public function getKba()
    {
        return $this->kba;
    }

    /**
     * @param KBA $kba
     *
     * @return $this
     */
    public function setKba($kba)
    {
        $this->kba = $kba;

        return $this;
    }
}
