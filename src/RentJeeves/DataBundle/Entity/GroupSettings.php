<?php
namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use RentJeeves\DataBundle\Model\GroupSettings as Base;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Exception\LogicException;

/**
 * GroupSettings
 *
 * @ORM\Entity()
 * @ORM\Table(name="rj_group_settings")
 * @ORM\HasLifecycleCallbacks
 */
class GroupSettings extends Base
{
    /**
     * @TODO need test for this functional.
     * @ORM\PreUpdate()
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $changeSet = $args->getEntityChangeSet();

        if (!isset($changeSet['isIntegrated']) ||
            !isset($changeSet['isIntegrated'][0]) ||
            !isset($changeSet['isIntegrated'][1])
        ) {
            return;
        }

        $isIntegratedBefore = $changeSet['isIntegrated'][0];
        $isIntegratedNew = $changeSet['isIntegrated'][1];

        /**
         * Once a client is set up as integrated, do not allow to turn off afterwards.
         * https://credit.atlassian.net/wiki/display/RT/Tenant+Waiting+Room
         */
        if ($isIntegratedBefore && !$isIntegratedNew) {
            throw new LogicException("Once a client is set up as integrated, we not allow to turn off afterwards.");
        }
    }
}
