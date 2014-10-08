<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\CoreBundle\Mailer\Mailer;
use RentJeeves\CoreBundle\Services\ContractProcess;
use RentJeeves\CoreBundle\Services\PropertyProcess;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use CreditJeeves\CoreBundle\Translation\Translator;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingCsv;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageCsv;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Validator;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Operation;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Contract;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Property;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Resident;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Tenant;
use RentJeeves\LandlordBundle\Accounting\Import\EntityManager\Unit;
use RentJeeves\LandlordBundle\Accounting\Import\Form\FormBind;
use RentJeeves\LandlordBundle\Accounting\Import\Form\Forms;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.import.handler.csv")
 */
class HandlerCsv extends HandlerAbstract
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
}
