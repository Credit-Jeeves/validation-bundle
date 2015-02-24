<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingCsv;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageCsv;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.handler.csv")
 */
class HandlerCsv extends HandlerAbstract
{

    protected $lines = [];

    /**
     * @InjectParams({
     *     "translator"       = @Inject("translator"),
     *     "sessionUser"      = @Inject("core.session.landlord"),
     *     "storage"          = @Inject("accounting.import.storage.csv"),
     *     "mapping"          = @Inject("accounting.import.mapping.csv")
     * })
     */
    public function __construct(
        Translator $translator,
        SessionUser $sessionUser,
        StorageCsv $storage,
        MappingCsv $mapping
    ) {
        $this->user             = $sessionUser->getUser();
        $this->group            = $sessionUser->getGroup();
        $this->translator       = $translator;
        $this->storage          = $storage;
        $this->mapping          = $mapping;
        parent::__construct();
    }

    public function updateMatchedContracts()
    {
        if (!$this->storage->isOnlyException()) {
            return false;
        }

        $this->lines = [];

        $filePath = $this->storage->getFilePath();
        $newFilePath = $this->getNewFilePath();
        $this->copyHeader($newFilePath);
        $self = $this;
        $total = $this->mapping->getTotal();


        $callbackSuccess = function () use ($self, $filePath) {
            $self->removeLastLineInFile($filePath);
        };

        $callbackFailed = function () use ($self, $filePath) {
            $self->moveLine($filePath);
        };

        for ($i = 1; $i <= $total; $i++) {
            $this->updateMatchedContractsWithCallback(
                $callbackSuccess,
                $callbackFailed
            );
        }

        krsort($this->lines);
        file_put_contents($newFilePath, implode('', $this->lines), FILE_APPEND | LOCK_EX);
        $this->storage->setFilePath(basename($newFilePath));

        return true;
    }

    public function copyHeader($newFilePath)
    {
        $lines = file($this->storage->getFilePath());
        $firstLine = reset($lines);
        file_put_contents($newFilePath, $firstLine, FILE_APPEND | LOCK_EX);

        return $newFilePath;
    }

    public function getNewFilePath()
    {
        return $this->storage->getFileDirectory().uniqid().".csv";
    }
}
