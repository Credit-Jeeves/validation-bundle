<?php

namespace RentJeeves\PublicBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Invite;
use RentJeeves\DataBundle\Entity\Landlord;

/**
 * @Service("reminder.invite")
 */
class ReminderInvite
{
    protected $error;

    protected $em;

    protected $i18n;

    protected $session;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @InjectParams({
     *     "em"             = @Inject("doctrine.orm.entity_manager"),
     *     "translator"     = @Inject("translator"),
     *     "session"        = @Inject("session"),
     *     "mailer"         = @Inject("project.mailer"),
     * })
     */
    public function __construct($em, $translator, $session, $mailer)
    {
        $this->em = $em;
        $this->i18n = $translator;
        $this->session = $session;
        $this->mailer = $mailer;
    }

    protected function setError($message)
    {
        $this->error = $this->i18n->trans($message);
    }

    public function getError()
    {
        return $this->error;
    }

    protected function isAlreadySend($contractId, $key)
    {
        $reminderList = $this->session->get($key);
        if (!$reminderList) {
            $reminderList = array();
        } else {
            $reminderList = json_decode($reminderList, true);
        }
        if (in_array($contractId, $reminderList)) {
            $this->setError('contract.reminder.error.already.send');

            return true;
        }

        $reminderList[] = $contractId;
        $this->session->set($key, json_encode($reminderList));

        return false;
    }

    public function sendTenant($contractId, $user, $currentGroup = null)
    {
        if ($this->isAlreadySend($contractId, 'tenant_reminder')) {
            return false;
        }

        $contract = $this->em->getRepository('RjDataBundle:Contract')->find($contractId);

        if (!$contract) {
            $this->setError('contract.not.found');

            return false;
        }

        if (!is_null($currentGroup)) {
            if ($contract->getGroupId() !== $currentGroup->getId()) {
                $this->setError('contract.not.found');

                return false;
            }
        }

        $tenant = $contract->getTenant();

        if (false === $tenant->getIsActive()) {
            $this->mailer->sendRjTenantInviteReminder($tenant, $user, $contract);
        }

        return true;
    }

    public function sendLandlord(Landlord $landlord)
    {
        $groups = $landlord->getAgentGroups();
        $contract = null;
        /**
         * @var $group Group
         */
        foreach ($groups as $group) {
            $contracts = $group->getContracts();
            if (!empty($contracts)) {
                /**
                 * @var $contract Contract
                 */
                $contract = $contracts->first();
                break;
            }
        }

        if (is_null($contract)) {
            $this->setError('contract.not.found');

            return false;
        }

        $tenant = $contract->getTenant();

        if ($this->isAlreadySend($contract->getId(), 'landlord_reminder')) {
            return false;
        }

        $this->mailer->sendRjLandLordInvite($landlord, $tenant, $contract);

        return true;
    }
}
