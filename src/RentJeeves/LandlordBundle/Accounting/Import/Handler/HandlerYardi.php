<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingYardi;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageYardi;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.handler.yardi")
 */
class HandlerYardi extends HandlerAbstract
{
    /**
     * @InjectParams({
     *     "translator"       = @Inject("translator"),
     *     "sessionUser"      = @Inject("core.session.landlord"),
     *     "storage"          = @Inject("accounting.import.storage.yardi"),
     *     "mapping"          = @Inject("accounting.import.mapping.yardi")
     * })
     */
    public function __construct(
        Translator $translator,
        SessionUser $sessionUser,
        StorageYardi $storage,
        MappingYardi $mapping
    ) {
        $this->user = $sessionUser->getUser();
        $this->group = $sessionUser->getGroup();
        $this->storage = $storage;
        $this->mapping = $mapping;
        $this->translator = $translator;
        parent::__construct();
    }

    public function updateMatchedContracts()
    {
        $self = $this;
        $this->updateMatchedContractsWithCallback(
            function () use ($self) {
                $self->removeLastLineInFile();
            },
            function () {
            }
        );
    }
}
