<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\AMSISettings as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_amsi_settings")
 */
class AMSISettings extends Base implements SettingsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return ['url' => $this->getUrl()];
    }
}
