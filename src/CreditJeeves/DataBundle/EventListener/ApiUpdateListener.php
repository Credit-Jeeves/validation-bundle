<?php

namespace CreditJeeves\DataBundle\EventListener;

use CreditJeeves\DataBundle\Entity\ApiUpdate;
use CreditJeeves\DataBundle\Entity\Dealer;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Tag;
use JMS\DiExtraBundle\Annotation\Inject;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @Service("data.event_listener.api_update_user.doctrine")
 * @Tag(
 *     "doctrine.event_listener",
 *     attributes={
 *         "event"="postUpdate",
 *         "method"="postUpdate"
 *     }
 * )
 */
class ApiUpdateListener
{
    /** @Inject("%api.admin_dealer_code%") */
    public $dealerCode;

    /** @Inject("api.data") */
    public $apiData;

    /**
     * On this method we need fill api update table if user or his lead was updated
     * it's only for user each have dealer with invite code $dealerCode
     *
     * @param LifecycleEventArgs $eventArgs
     * @return bool
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $user = false;
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof User) {
            $user = $entity;
        }

        if ($entity instanceof Lead) {
            $user = $entity->getUser();
        }

        if (!$user) {
            return false;
        }

        /** @var $dealer Dealer */
        $dealer = $em->getRepository('CreditJeeves\DataBundle\Entity\Dealer')->findOneBy(
            array(
                'invite_code' => $this->dealerCode,
            )
        );

        if (!$dealer) {
            return false;
        }

        try {
            /** @var $holding Holding */
            $holding = $this->apiData->getHolding($dealer);
        } catch(HttpException $e) {
            return false;
        }

        $groups = $holding->getGroups();
        $leads = $user->getUserLeads();

        $needUpdate = false;

        /** @var $lead Lead */
        foreach($leads as $lead) {
            /** @var $group Group */
            foreach($groups as $group) {
                if ($group->getId() === $lead->getCjGroupId()) {
                    $needUpdate = true;
                    break;
                }
            }

            if ($needUpdate) {
                break;
            }
        }

        if (!$needUpdate) {
            return false;
        }

        $userUpdateApiBundle = $em->getRepository('DataBundle:ApiUpdate')->findOneBy(
            array(
                'user' => $user->getId(),
            )
        );

        if ($userUpdateApiBundle) {
            return false;
        }

        $apiUpdate = new ApiUpdate();
        $apiUpdate->setUser($user);

        $em->persist($apiUpdate);
        $em->flush();

        return true;
    }
}