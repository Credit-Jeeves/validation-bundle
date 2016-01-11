<?php

namespace RentJeeves\LandlordBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\ApiIntegrationType;
use RentJeeves\DataBundle\Enum\ImportSource;
use Symfony\Component\Translation\Translator;

/**
 * import.settings.validator
 */
class ImportSettingsValidator
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var string
     */
    protected $errorMessage = null;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Group $currentGroup
     * @return boolean
     */
    public function isValidImportSettings(Group $currentGroup)
    {
        $this->errorMessage = null;
        if (!$currentGroup->isExistImportSettings()) {
            $this->errorMessage = $this->translator->trans('import.error.settings_missing');

            return false;
        }

        $apiIntegrationType = $currentGroup->getHolding()->getApiIntegrationType();
        $importSettings = $currentGroup->getImportSettings();

        if ($apiIntegrationType !== ApiIntegrationType::NONE && $importSettings->getSource() === ImportSource::CSV) {
            $this->errorMessage = $this->translator->trans('import.error.settings_is_wrong');

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
