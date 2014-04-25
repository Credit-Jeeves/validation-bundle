<?php

namespace RentJeeves\LandlordBundle\Accounting;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.permission")
 */
class AccountingPermission
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @InjectParams({
     *     "context" = @Inject("security.context")
     * })
     */
    public function __construct(SecurityContext $context)
    {
        $this->user = $context->getToken()->getUser();
    }

    /**
     * @return bool
     */
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

        return $setting->getIsIntegrated();
    }

    /**
     * @return bool
     */
    public function hasAccessToExport()
    {
        if (!$this->user->getSettings()) {
            return false;
        }

        return $this->user->getSettings()->getIsBaseOrderReport();
    }

    /**
     * @return bool
     */
    public function hasAccessToAccountingTab()
    {
        if ($this->hasAccessToExport() || $this->hasAccessToImport()) {
            return true;
        }

        return false;
    }
}
