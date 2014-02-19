<?php

namespace RentJeeves\LandlordBundle\Services;

use JMS\DiExtraBundle\Annotation\Service;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @Service("reminder.invite")
 */
class ReminderInvite
{
    protected $error;

    protected $em;

    protected $i18n;

    protected $session;

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

    public function send($contractId, $user, $currentGroup = null)
    {
        $landlordReminder = $this->session->get('landlord_reminder');
        if (!$landlordReminder) {
            $landlordReminder = array();
        } else {
            $landlordReminder = json_decode($landlordReminder, true);
        }

        if (in_array($contractId, $landlordReminder)) {
            $this->setError('contract.reminder.error.already.send');
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

        if ($tenant->getIsActive()) {
            $this->mailer->sendRjTenantInviteReminderPayment($tenant, $user, $contract);
        } else {
            $this->mailer->sendRjTenantInviteReminder($tenant, $user, $contract);
        }

        $landlordReminder[] = $contract->getId();

        $this->session->set('landlord_reminder', json_encode($landlordReminder));

        return true;
    }
}
