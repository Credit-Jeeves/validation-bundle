<?php
namespace CreditJeeves\ExperianBundle\Model;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\XmlRoot("ARF")
 */
class ARF
{
    /**
     * @Serializer\SerializedName("ARFVersion")
     * @Serializer\Groups({"CreditProfile"})
     * @var string
     */
    protected $arfVersion = '07';

    /**
     * @Serializer\SerializedName("Y2K")
     * @Serializer\Groups({"CreditProfile"})
     * @var string
     */
    protected $y2k = 'Y';

    /**
     * @Serializer\SerializedName("Segment130")
     * @Serializer\Groups({"CreditProfile"})
     * @var string
     */
    protected $segment130 = 'Y';

    /**
     * @return string
     */
    public function getArfVersion()
    {
        return $this->arfVersion;
    }

    /**
     * @param string $arfVersion
     *
     * @return $this
     */
    public function setArfVersion($arfVersion)
    {
        $this->arfVersion = $arfVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getY2k()
    {
        return $this->y2k;
    }

    /**
     * @param string $y2k
     *
     * @return $this
     */
    public function setY2k($y2k)
    {
        $this->y2k = $y2k;

        return $this;
    }

    /**
     * @return string
     */
    public function getSegment130()
    {
        return $this->segment130;
    }

    /**
     * @param string $segment130
     *
     * @return $this
     */
    public function setSegment130($segment130)
    {
        $this->segment130 = $segment130;

        return $this;
    }
}
