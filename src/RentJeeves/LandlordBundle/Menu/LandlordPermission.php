<?php

namespace RentJeeves\LandlordBundle\Menu;

use CreditJeeves\CoreBundle\Session\User as SessionUser;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\ImportSource;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("landlord.permission")
 */
class LandlordPermission
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @InjectParams({
     *     "sessionUser" = @Inject("core.session.landlord")
     * })
     */
    public function __construct(SessionUser $sessionUser)
    {
        $this->user = $sessionUser->getUser();
        $this->group = $sessionUser->getGroup();
    }

    /**
     * @return bool
     */
    public function hasAccessToImport()
    {
        $setting = $this->group->getGroupSettings();

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

    /**
     * @return bool
     */
    public function hasAccessToPropertiesTab()
    {
        if (null !== $this->group && $this->group->getGroupSettings()->isShowPropertiesTab()) {
            return true;
        }

        return false;
    }
}
