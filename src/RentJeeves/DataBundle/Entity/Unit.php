<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Unit as Base;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs as BaseLifecycleEventArgs;

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
    const SINGLE_PROPERTY_UNIT_NAME = 'SYSTEM';
    const SEARCH_PROPERTY_NEW_NAME = 'NEW';

    public function __toString()
    {
        return $this->getName();
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
