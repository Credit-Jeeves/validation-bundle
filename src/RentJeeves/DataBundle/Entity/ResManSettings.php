<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ResManSettings as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="resman_settings")
 * @ORM\HasLifecycleCallbacks
 */
class ResManSettings extends Base implements SettingsInterface
{
    public function getParameters()
    {
        return array(
            'AccountID' => $this->getAccountId()
        );
    }
}
