<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\MRISettings as Base;
use Doctrine\ORM\Mapping as ORM;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_mri_settings")
 */
class MRISettings extends Base implements SettingsInterface
{
    public function setAllowToSendRealTime()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowToSendRealTime()
    {
        return true;
    }

    public function getParameters()
    {
        $data = sprintf(
            '%s/%s/%s/%s:%s',
            $this->getClientId(),
            $this->getDatabaseName(),
            $this->getUser(),
            $this->getPartnerKey(),
            $this->getPassword()
        );

        return [
            'authorization' => base64_encode($data),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isMultiProperty()
    {
        return true;
    }
}
