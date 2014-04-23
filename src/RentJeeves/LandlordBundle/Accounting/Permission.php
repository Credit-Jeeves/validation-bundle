<?php

namespace RentJeeves\LandlordBundle\Accounting;

use CreditJeeves\DataBundle\Entity\Group;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.permission")
 */
class Permission
{
    /**
     * @InjectParams({
     *     "context" = @Inject("security.context")
     * })
     */
    public function __construct(SecurityContext $context)
    {
        $this->user = $context->getToken()->getUser();
    }

    public function hasAccessToImport()
    {
        /**
         * @var $currentGroup Group
         */
        $currentGroup = $this->user->getCurrentGroup();
        if (empty($currentGroup)) {
            return false;
        }

        $setting = $currentGroup->getGroupSettings();

        if (empty($setting)) {
            return false;
        }

        if ($setting->getIsIntegrated()) {
            return true;
        }

        return false;
    }

    public function hasAccessToExport()
    {
        if (!$this->user->getSettings()) {
            return false;
        }

        if ($this->user->getSettings()->getIsBaseOrderReport()) {
            return true;
        }

        return false;
    }

    public function hasAccessToAccountingTab()
    {
        if ($this->hasAccessToExport() || $this->hasAccessToImport()) {
            return true;
        }

        return false;
    }
}
