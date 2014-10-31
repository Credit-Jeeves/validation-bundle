<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingYardi;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageYardi;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Operation;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Property;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Resident;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Unit;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Contract;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Tenant;
use RentJeeves\LandlordBundle\Accounting\Import\Form\FormBind;
use RentJeeves\LandlordBundle\Accounting\Import\Form\Forms;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.handler.yardi")
 */
class HandlerYardi extends HandlerAbstract
{
    use Forms;
    use Contract;
    use Tenant;
    use Resident;
    use Property;
    use Operation;
    use FormBind;
    use Unit;

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
        $this->user             = $sessionUser->getUser();
        $this->group            = $sessionUser->getGroup();
        $this->storage          = $storage;
        $this->mapping          = $mapping;
        $this->translator       = $translator;
    }
}
