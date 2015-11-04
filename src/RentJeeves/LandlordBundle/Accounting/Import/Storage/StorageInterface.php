<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use Symfony\Component\Form\FormInterface;

interface StorageInterface
{
    /**
     * @param FormInterface $form
     * @return void
     */
    public function setImportData(FormInterface $form);

    public function getImportData();

    public function getStorageType();

    public function setStorageType($type);

    public function isMultipleProperty();

    public function isMultipleGroup();

    public function isValid();

    public function clearSession();

    public function clearDataBeforeReview();
}
