<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Model\Unit as Base;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;
use JMS\Serializer\Annotation as Serializer;

/**
 * Property
 *
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\UnitRepository")
 * @ORM\Table(name="rj_unit")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 * @ORM\HasLifecycleCallbacks()
 */
class Unit extends Base
{
    const SINGLE_PROPERTY_UNIT_NAME = 'SINGLE_PROPERTY';
    const SEARCH_UNIT_UNASSIGNED = 'UNASSIGNED';
    const SEARCH_PROPERTY_NEW_NAME = 'NEW';

    public function getName()
    {
        $name = parent::getName();
        if (static::SINGLE_PROPERTY_UNIT_NAME == $name) {
            return '';
        }

        $isIntegratedWithBuildingId = $this->isIntegratedWithBuildingId();
        $unitId = ($unitMapping = $this->getUnitMapping()) ? $unitMapping->getExternalUnitId() : '';
        /** @link https://credit.atlassian.net/browse/RT-1476  MRI Unit name causing confusion */
        /** @link https://credit.atlassian.net/browse/RT-1579 refactoring by link logic */
        if ($isIntegratedWithBuildingId && $this->getProperty()->isMultipleBuildings() && !empty($unitId)) {
            $names = explode('|', $unitId);

            return isset($names[1]) ? $names[1].$this->name : $this->name;
        }

        return $name;
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("name")
     * @Serializer\Groups({"AdminUnit"})
     */
    public function getActualName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    protected function isIntegratedWithBuildingId()
    {
        $holding = $this->getGroup() ? $this->getGroup()->getHolding() : null;
        $apiIntegrationType = $holding ? $holding->getApiIntegrationType() : null;

        return $apiIntegrationType === ApiIntegrationType::MRI || $apiIntegrationType === ApiIntegrationType::RESMAN;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Documentation link https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
     * @ORM\PostRemove
     */
    public function deleteAllWaitingContracts(BaseLifecycleEventArgs $args)
    {
        $contractsWaiting = $this->getContractsWaiting();

        if (empty($contractsWaiting)) {
            return;
        }

        $em = $args->getEntityManager();
        foreach ($contractsWaiting as $contractWaiting) {
            $em->remove($contractWaiting);
        }

        $em->flush();
    }
}
