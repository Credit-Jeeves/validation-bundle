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
    }

    public function updateMatchedContracts()
    {
        if (!$this->storage->isOnlyException()) {
            return false;
        }

        $newFilePath = $this->getNewFilePath();
        $this->copyHeader($newFilePath);
        $self = $this;
        $total = $this->mapping->getTotal();
        for ($i = 1; $i <= $total; $i++) {
            $this->updateMatchedContractsWithCallback(
                function () use ($self) {
                    $self->removeLastLineInFile();
                },
                function () use ($self, $newFilePath) {
                    $self->moveLine($newFilePath);
                }
            );
        }

        $this->storage->setFilePath(basename($newFilePath));

        return true;
    }

    public function moveLine($newFilePath)
    {
        $lines = file($this->storage->getFilePath());
        $last = sizeof($lines) - 1 ;
        $lastLine = $lines[$last];
        file_put_contents($newFilePath, $lastLine, FILE_APPEND | LOCK_EX);

        $this->removeLastLineInFile();
    }

    public function copyHeader($newFilePath)
    {
        $data = $this->mapping->getDataForMapping(0, 1, false);
        $header = array_keys($data[1]);
        $file = fopen($newFilePath, "w");
        fputcsv($file, $header);
        fclose($file);

        return $newFilePath;
    }

    public function getNewFilePath()
    {
        return $this->storage->getFileDirectory().uniqid().".csv";
    }
}
