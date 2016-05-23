<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use Doctrine\ORM\EntityManager;
use CreditJeeves\DataBundle\Entity\Group as EntityGroup;
use RentJeeves\CoreBundle\Session\Landlord as SessionUser;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as ImportMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Storage\StorageInterface;

/**
 * @property EntityManager $em
 * @property StorageInterface $storage
 * @property EntityGroup $group
 * @property SessionUser $user
 */
trait Group
{
    /**
     * @param array $row
     * @return EntityGroup|null
     * @throws \Exception
     */
    protected function getGroup(array $row)
    {
        if (!$this->storage->isMultipleGroup()) {
            return $this->group;
        }
        // If we don't initialize rows yet, we should use current group
        // Like example when we initialize forms
        // We call isSupportResident from group
        // but we don't initialize data row and don't have KEY_GROUP_ACCOUNT_NUMBER key yet.
        if (empty($row)) {
            return $this->group;
        }

        return $this
            ->em
            ->getRepository('DataBundle:Group')
            ->getGroupByAccountNumber(
                $row[ImportMapping::KEY_GROUP_ACCOUNT_NUMBER],
                $this->user->getHolding()
            );
    }
}
