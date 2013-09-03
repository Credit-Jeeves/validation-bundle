<?php
namespace RentJeeves\DataBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Enum\ContractStatus;

/**
 * @author Ton Sharp <66ton99@gmail.com>
 *
 * @Service("data.event_listener.doctrine")
 * @Tag("doctrine.event_listener", attributes = { "event" = "prePersist", "method" = "prePersist" })
 */
class Doctrine
{

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();
        if ($entity instanceof DepositAccount) {
            //@TODO need check this method, becouse I am write code, but not check it.
            $depositAccount = $entity;
            $group = $depositAccount->getGroup();
            $holding = $group->getHolding();
            $landlords = $holding->getDealers();

            foreach ($landlords as $landlord) {
                $groups = $landlord->getAgentGroups();
                if (!$groups->contains($group)) {
                    continue;
                }

                $contractsLandlord = $em->getRepository('RjDataBundle:Contract')->getContractsLandlord($landlord);
                if (empty($contractsLandlord)) {
                    continue;
                }

                foreach ($contractsLandlord as $contract) {
                    $tenant = $contract->getTenant();
                    if ($tenant->getIsActive() && $contract->getStatus() == ContractStatus::INVITE) {
                        $contract->setStatus(ContractStatus::PENDING);
                        $em->persist($contract);
                    }
                }
            }

            $em->flush();
        }
    }
}
