<?php

namespace RentJeeves\DataBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\DataBundle\Enum\PaymentTypeACH;
use RentJeeves\DataBundle\Enum\PaymentTypeCC;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class YardiSettings
{
    /**
     * @ORM\Column(
     *     name="id",
     *     type="bigint"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(
     *     name="url",
     *     type="encrypt",
     *     nullable=false
     * )
     * @Assert\Url()
     * @Assert\Regex(
     *     pattern = "/\/$/",
     *     message="yardi.regexp.error.url"
     * )
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(
     *     name="username",
     *     type="encrypt",
     *     nullable=false
     * )
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(
     *     name="password",
     *     type="encrypt",
     *     nullable=false
     * )
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(
     *     name="database_server",
     *     type="encrypt",
     *     nullable=false
     * )
     * @var string
     */
    protected $databaseServer;

    /**
     * @ORM\Column(
     *     name="database_name",
     *     type="encrypt",
     *     nullable=false
     * )
     * @var string
     */
    protected $databaseName;

    /**
     * @ORM\Column(
     *     name="platform",
     *     type="encrypt",
     *     nullable=false
     * )
     * @var string
     */
    protected $platform;

    /**
     * @ORM\Column(
     *     type="PaymentTypeACH",
     *     name="payment_type_ach",
     *     options={
     *          "default":"check"
     *     }
     * )
     */
    protected $paymentTypeACH = PaymentTypeACH::CHECK;

    /**
     * @ORM\Column(
     *     type="PaymentTypeCC",
     *     name="payment_type_cc",
     *     options={
     *          "default":"other"
     *     }
     * )
     */
    protected $paymentTypeCC = PaymentTypeCC::OTHER;

    /**
     * @ORM\Column(
     *      type="encrypt",
     *      name="notes_ach",
     *      nullable=true
     * )
     */
    protected $notesACH;

    /**
     * @ORM\Column(
     *      type="encrypt",
     *      name="notes_cc",
     *      nullable=true
     * )
     */
    protected $notesCC;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CreditJeeves\DataBundle\Entity\Holding",
     *     inversedBy="yardiSettings",
     *     cascade={"persist", "merge"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="holding_id", referencedColumnName="id", nullable=false, unique=true)
     * @var Holding
     */
    protected $holding;

    /**
     * @ORM\Column(
     *      type="boolean",
     *      name="sync_balance",
     *      options={
     *          "default":0
     *      }
     * )
     */
    protected $syncBalance = false;

    /**
     * @param string $notesACH
     */
    public function setNotesACH($notesACH)
    {
        $this->notesACH = $notesACH;
    }

    /**
     * @return string
     */
    public function getNotesACH()
    {
        return $this->notesACH;
    }

    /**
     * @param string $notesCC
     */
    public function setNotesCC($notesCC)
    {
        $this->notesCC = $notesCC;
    }

    /**
     * @return string
     */
    public function getNotesCC()
    {
        return $this->notesCC;
    }

    /**
     * @param string $paymentTypeACH
     */
    public function setPaymentTypeACH($paymentTypeACH)
    {
        $this->paymentTypeACH = $paymentTypeACH;
    }

    /**
     * @return string
     */
    public function getPaymentTypeACH()
    {
        return $this->paymentTypeACH;
    }

    /**
     * @param string $paymentTypeCC
     */
    public function setPaymentTypeCC($paymentTypeCC)
    {
        $this->paymentTypeCC = $paymentTypeCC;
    }

    /**
     * @return string
     */
    public function getPaymentTypeCC()
    {
        return $this->paymentTypeCC;
    }

    /**
     * @param string $databaseName
     */
    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param string $databaseServer
     */
    public function setDatabaseServer($databaseServer)
    {
        $this->databaseServer = $databaseServer;
    }

    /**
     * @return string
     */
    public function getDatabaseServer()
    {
        return $this->databaseServer;
    }

    /**
     * @param Holding $holding
     */
    public function setHolding(Holding $holding)
    {
        $this->holding = $holding;
    }

    /**
     * @return Holding
     */
    public function getHolding()
    {
        return $this->holding;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $syncBalance
     */
    public function setSyncBalance($syncBalance)
    {
        $this->syncBalance = $syncBalance;
    }

    /**
     * @return mixed
     */
    public function getSyncBalance()
    {
        return $this->syncBalance;
    }
}
