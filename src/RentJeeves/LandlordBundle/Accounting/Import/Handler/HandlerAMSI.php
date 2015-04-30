<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAMSI;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageAMSI;

/**
 * @Service("accounting.import.handler.amsi")
 */
class HandlerAMSI extends HandlerAbstract
{
    /**
     * @InjectParams({
     *     "translator" = @Inject("translator"),
     *     "sessionUser" = @Inject("core.session.landlord"),
     *     "storage" = @Inject("accounting.import.storage.amsi"),
     *     "mapping" = @Inject("accounting.import.mapping.amsi")
     * })
     */
    public function __construct(
        Translator $translator,
        SessionUser $sessionUser,
        StorageAMSI $storage,
        MappingAMSI $mapping
    ) {
        $this->user = $sessionUser->getUser();
        $this->group = $sessionUser->getGroup();
        $this->storage = $storage;
        $this->mapping = $mapping;
        $this->translator = $translator;
        parent::__construct();
    }
}
