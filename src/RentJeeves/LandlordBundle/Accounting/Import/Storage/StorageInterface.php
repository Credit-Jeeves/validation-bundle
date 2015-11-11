<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use RentJeeves\DataBundle\Entity\ImportGroupSettings;
use Symfony\Component\Form\FormInterface;

interface StorageInterface
{
    /**
     * @param ImportGroupSettings $importGroupSettings
     * @param FormInterface $form
     * @return void
     */
    public function setImportData(ImportGroupSettings $importGroupSettings, FormInterface $form = null);

    public function getImportData();

    public function getStorageType();

    public function setStorageType($type);

    public function isMultipleProperty();

    public function isMultipleGroup();

    public function isValid();

    public function clearSession();

    public function clearDataBeforeReview();
}
