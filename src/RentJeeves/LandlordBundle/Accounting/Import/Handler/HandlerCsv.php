<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\DataBundle\Enum\PaymentAccepted;
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
        parent::__construct();
    }

    /**
     * @{inheritdoc}
     */
    protected function setPaymentAccepted(array $row)
    {
        if (!isset($row[MappingCsv::KEY_PAYMENT_ACCEPTED])) {
            return;
        }
        //We have negative logic for CSV
        if (strtolower($row[MappingCsv::KEY_PAYMENT_ACCEPTED]) === 'y') {
            $row[MappingCsv::KEY_PAYMENT_ACCEPTED] = PaymentAccepted::DO_NOT_ACCEPT;
        }

        if (strtolower($row[MappingCsv::KEY_PAYMENT_ACCEPTED]) === 'n') {
            $row[MappingCsv::KEY_PAYMENT_ACCEPTED] = PaymentAccepted::ANY;
        }

        parent::setPaymentAccepted($row);
    }
}
