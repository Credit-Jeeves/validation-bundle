<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\ResManSettings as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="resman_settings")
 */
class ResManSettings extends Base implements SettingsInterface
{
    /**
     * {@inheritdoc}
     */
    public function isAllowedToSendRealTimePayments()
    {
        return true;
    }

    public function getParameters()
    {
        return array(
            'AccountID' => $this->getAccountId()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isMultiProperty()
    {
        return true;
    }
}
