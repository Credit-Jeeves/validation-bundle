<?php

namespace RentJeeves\ImportBundle\LeaseImport\Loader;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\ImportLease;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\ImportType;
use RentJeeves\ImportBundle\LeaseImport\Loader\Lease\LeaseInterface;
use RentJeeves\ImportBundle\LeaseImport\Loader\Resident\ResidentInterface;

class LeaseLoadContext
{
    protected $importType = ImportType::MULTI_PROPERTIES; // single-group or multi-group

    /** @var ImportLease $importData */
    protected $importData;

    /** @var Group $group */
    protected $group;

    /** @var Unit $unit */
    protected $unit;

    /** @var ResidentInterface $resident */
    protected $resident;

    /** @var LeaseInterface $lease */
    protected $lease;

    /** @var ImportLeaseFieldBitmap $leaseFieldsBitmap */
    protected $leaseFieldsBitmap;

    /** @var ImportLeaseStatusBitmap $leaseStatusBitmap */
    protected $leaseStatusBitmap;

    /** @var ImportResidentFieldBitmap $residentFieldsBitmap */
    protected $residentFieldsBitmap;

    /** @var ImportResidentStatusBitmap $residentStatusBitmap */
    protected $residentStatusBitmap;
}
