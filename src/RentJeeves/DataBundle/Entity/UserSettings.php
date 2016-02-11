<?php

namespace RentJeeves\DataBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use RentJeeves\DataBundle\Model\UserSettings as Base;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rj_user_settings")
 * @ORM\Entity(repositoryClass="RentJeeves\DataBundle\Entity\UserSettingsRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class UserSettings extends Base
{
    public function isCreditTrack()
    {
        return !!$this->getCreditTrackEnabledAt();
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        $oldValue = $this->getCreditTrackPaymentAccount();
        $this->setCreditTrackPaymentAccount(null);
        $em->persist($this);
        $uow->propertyChanged($this, 'creditTrackPaymentAccount', $oldValue, null);
        $uow->scheduleExtraUpdate($this, array('creditTrackPaymentAccount' => array($oldValue, null)));
    }


    /**
     * @return bool
     */
    public function isScoreTrackFree()
    {
        $scoreTrackFreeUntil = $this->getScoretrackFreeUntil();
        if (empty($scoreTrackFreeUntil)) {
            return false;
        }

        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $scoreTrackFreeUntil->setTime(0, 0, 0);

        if ($today >= $scoreTrackFreeUntil) {
            return false;
        }

        return true;
    }
}
