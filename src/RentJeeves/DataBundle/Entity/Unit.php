<?php
namespace RentJeeves\DataBundle\Entity;

use RentJeeves\DataBundle\Model\Unit as Base;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use \Doctrine\ORM\Event\LifecycleEventArgs;

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
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @TODO need test for it.
     * Documentation link https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
     * @ORM\PostRemove
     */
    public function deleteAllWaitingContract(LifecycleEventArgs $args)
    {
        $contractsWaiting = $this->getContractsWaiting();
        $em = $args->getEntityManager();

        if (empty($contractsWaiting)) {
            return;
        }

        foreach ($contractsWaiting as $contractWaiting) {
            $em->remove($contractWaiting);
        }

        $em->flush();
    }
}
