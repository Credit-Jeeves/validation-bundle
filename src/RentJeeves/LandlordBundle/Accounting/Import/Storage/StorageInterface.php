<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Storage;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Monolog\Logger;

interface StorageInterface
{
    public function __construct(Session $session, Logger $logger);
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
