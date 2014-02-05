<?php

namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("SharedApplication")
 */
class SharedApplication
{
    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule1")
     * @Serializer\SerializedName("GLBRule1")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule1;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule2")
     * @Serializer\SerializedName("GLBRule2")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule2;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule3")
     * @Serializer\SerializedName("GLBRule3")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule3;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule4")
     * @Serializer\SerializedName("GLBRule4")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule4;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule5")
     * @Serializer\SerializedName("GLBRule5")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule5;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule6")
     * @Serializer\SerializedName("GLBRule6")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule6;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule7")
     * @Serializer\SerializedName("GLBRule7")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule7;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule8")
     * @Serializer\SerializedName("GLBRule8")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule8;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule9")
     * @Serializer\SerializedName("GLBRule9")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule9;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule10")
     * @Serializer\SerializedName("GLBRule10")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule10;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule11")
     * @Serializer\SerializedName("GLBRule11")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule11;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule12")
     * @Serializer\SerializedName("GLBRule12")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule12;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule13")
     * @Serializer\SerializedName("GLBRule13")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule13;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule14")
     * @Serializer\SerializedName("GLBRule14")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule14;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule15")
     * @Serializer\SerializedName("GLBRule15")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule15;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule16")
     * @Serializer\SerializedName("GLBRule16")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule16;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule17")
     * @Serializer\SerializedName("GLBRule17")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule17;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule18")
     * @Serializer\SerializedName("GLBRule18")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule18;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule19")
     * @Serializer\SerializedName("GLBRule19")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule19;

    /**
     * @Serializer\Type("CreditJeeves\ExperianBundle\Model\GLBRule20")
     * @Serializer\SerializedName("GLBRule20")
     * @Serializer\Groups({"CreditJeeves"})
     */
    protected $GLBRule20;

    /**
     * @param mixed $GLBRule1
     */
    public function setGLBRule1($GLBRule1)
    {
        $this->GLBRule1 = $GLBRule1;
    }

    /**
     * @return mixed
     */
    public function getGLBRule1()
    {
        return $this->GLBRule1;
    }

    /**
     * @param mixed $GLBRule10
     */
    public function setGLBRule10($GLBRule10)
    {
        $this->GLBRule10 = $GLBRule10;
    }

    /**
     * @return mixed
     */
    public function getGLBRule10()
    {
        return $this->GLBRule10;
    }

    /**
     * @param mixed $GLBRule11
     */
    public function setGLBRule11($GLBRule11)
    {
        $this->GLBRule11 = $GLBRule11;
    }

    /**
     * @return mixed
     */
    public function getGLBRule11()
    {
        return $this->GLBRule11;
    }

    /**
     * @param mixed $GLBRule12
     */
    public function setGLBRule12($GLBRule12)
    {
        $this->GLBRule12 = $GLBRule12;
    }

    /**
     * @return mixed
     */
    public function getGLBRule12()
    {
        return $this->GLBRule12;
    }

    /**
     * @param mixed $GLBRule13
     */
    public function setGLBRule13($GLBRule13)
    {
        $this->GLBRule13 = $GLBRule13;
    }

    /**
     * @return mixed
     */
    public function getGLBRule13()
    {
        return $this->GLBRule13;
    }

    /**
     * @param mixed $GLBRule14
     */
    public function setGLBRule14($GLBRule14)
    {
        $this->GLBRule14 = $GLBRule14;
    }

    /**
     * @return mixed
     */
    public function getGLBRule14()
    {
        return $this->GLBRule14;
    }

    /**
     * @param mixed $GLBRule15
     */
    public function setGLBRule15($GLBRule15)
    {
        $this->GLBRule15 = $GLBRule15;
    }

    /**
     * @return mixed
     */
    public function getGLBRule15()
    {
        return $this->GLBRule15;
    }

    /**
     * @param mixed $GLBRule16
     */
    public function setGLBRule16($GLBRule16)
    {
        $this->GLBRule16 = $GLBRule16;
    }

    /**
     * @return mixed
     */
    public function getGLBRule16()
    {
        return $this->GLBRule16;
    }

    /**
     * @param mixed $GLBRule17
     */
    public function setGLBRule17($GLBRule17)
    {
        $this->GLBRule17 = $GLBRule17;
    }

    /**
     * @return mixed
     */
    public function getGLBRule17()
    {
        return $this->GLBRule17;
    }

    /**
     * @param mixed $GLBRule18
     */
    public function setGLBRule18($GLBRule18)
    {
        $this->GLBRule18 = $GLBRule18;
    }

    /**
     * @return mixed
     */
    public function getGLBRule18()
    {
        return $this->GLBRule18;
    }

    /**
     * @param mixed $GLBRule19
     */
    public function setGLBRule19($GLBRule19)
    {
        $this->GLBRule19 = $GLBRule19;
    }

    /**
     * @return mixed
     */
    public function getGLBRule19()
    {
        return $this->GLBRule19;
    }

    /**
     * @param mixed $GLBRule2
     */
    public function setGLBRule2($GLBRule2)
    {
        $this->GLBRule2 = $GLBRule2;
    }

    /**
     * @return mixed
     */
    public function getGLBRule2()
    {
        return $this->GLBRule2;
    }

    /**
     * @param mixed $GLBRule20
     */
    public function setGLBRule20($GLBRule20)
    {
        $this->GLBRule20 = $GLBRule20;
    }

    /**
     * @return mixed
     */
    public function getGLBRule20()
    {
        return $this->GLBRule20;
    }

    /**
     * @param mixed $GLBRule3
     */
    public function setGLBRule3($GLBRule3)
    {
        $this->GLBRule3 = $GLBRule3;
    }

    /**
     * @return mixed
     */
    public function getGLBRule3()
    {
        return $this->GLBRule3;
    }

    /**
     * @param mixed $GLBRule4
     */
    public function setGLBRule4($GLBRule4)
    {
        $this->GLBRule4 = $GLBRule4;
    }

    /**
     * @return mixed
     */
    public function getGLBRule4()
    {
        return $this->GLBRule4;
    }

    /**
     * @param mixed $GLBRule5
     */
    public function setGLBRule5($GLBRule5)
    {
        $this->GLBRule5 = $GLBRule5;
    }

    /**
     * @return mixed
     */
    public function getGLBRule5()
    {
        return $this->GLBRule5;
    }

    /**
     * @param mixed $GLBRule6
     */
    public function setGLBRule6($GLBRule6)
    {
        $this->GLBRule6 = $GLBRule6;
    }

    /**
     * @return mixed
     */
    public function getGLBRule6()
    {
        return $this->GLBRule6;
    }

    /**
     * @param mixed $GLBRule7
     */
    public function setGLBRule7($GLBRule7)
    {
        $this->GLBRule7 = $GLBRule7;
    }

    /**
     * @return mixed
     */
    public function getGLBRule7()
    {
        return $this->GLBRule7;
    }

    /**
     * @param mixed $GLBRule8
     */
    public function setGLBRule8($GLBRule8)
    {
        $this->GLBRule8 = $GLBRule8;
    }

    /**
     * @return mixed
     */
    public function getGLBRule8()
    {
        return $this->GLBRule8;
    }

    /**
     * @param mixed $GLBRule9
     */
    public function setGLBRule9($GLBRule9)
    {
        $this->GLBRule9 = $GLBRule9;
    }

    /**
     * @return mixed
     */
    public function getGLBRule9()
    {
        return $this->GLBRule9;
    }

    public function getArrayOfErrors()
    {
        $attributs = get_object_vars($this);
        $array = array();
        foreach ($attributs as $name => $value) {
            $method = "get".ucfirst($name);
            $GLBRule = $this->$method();
            $value = $GLBRule->getDescription();
            $code = $GLBRule->getCode();

            if (!empty($value) && !empty($code)) {
                $array[$code] = $value;
            }
        }

        return $array;
    }
}
