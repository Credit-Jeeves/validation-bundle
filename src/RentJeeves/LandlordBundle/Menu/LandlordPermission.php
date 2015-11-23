<?php

namespace RentJeeves\LandlordBundle\Menu;

use CreditJeeves\CoreBundle\Session\User as SessionUser;
use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\User;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
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
        if (!$this->group->isExistImportSettings()) {
            return false;
        }
        $importSettings = $this->group->getImportSettings();
        $apiIntegrationType = $this->user->getHolding()->getApiIntegrationType();

        $isValidImportSettings = $apiIntegrationType !== ApiIntegrationType::NONE &&
            $importSettings->getSource() === ImportSource::CSV;

        if (empty($this->group)) {
            return false;
        }

        $setting = $this->group->getGroupSettings();

        if (empty($setting)) {
            return false;
        }

        return $setting->getIsIntegrated() && $isValidImportSettings;
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
