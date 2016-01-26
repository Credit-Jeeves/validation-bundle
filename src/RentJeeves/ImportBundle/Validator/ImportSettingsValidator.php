<?php

namespace RentJeeves\ImportBundle\Validator;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Enum\AccountingSystem;
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
     * @var string
     */
    protected $supportEmail;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator, $supportEmail)
    {
        $this->translator = $translator;
        $this->supportEmail = $supportEmail;
    }

    /**
     * @param Group $currentGroup
     * @return boolean
     */
    public function isValidImportSettings(Group $currentGroup)
    {
        $this->errorMessage = null;
        if (!$currentGroup->isExistImportSettings()) {
            $this->errorMessage = $this->translator->trans(
                'import.error.settings_missing',
                ['%support_email%' => $this->supportEmail]
            );

            return false;
        }

        $accountingSystem = $currentGroup->getHolding()->getAccountingSystem();
        $importSettings = $currentGroup->getImportSettings();

        if ($accountingSystem !== AccountingSystem::NONE && $importSettings->getSource() === ImportSource::CSV) {
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
