<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\YardiSettings as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\ExternalApiBundle\Soap\SoapSettingsInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="yardi_settings")
 * @ORM\HasLifecycleCallbacks
 */
class YardiSettings extends Base implements SoapSettingsInterface
{
    public function getTemplateParameters()
    {
        return array(
            'url' => $this->getUrl()
        );
    }
}
