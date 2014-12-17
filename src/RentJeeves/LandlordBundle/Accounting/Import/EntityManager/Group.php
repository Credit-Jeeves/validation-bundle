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
    protected function getGroup(array $row)
    {
        if (!$this->storage->isMultipleGroup()) {
            return $this->group;
        }

        return $this
            ->em
            ->getRepository('DataBundle:Group')
            ->getGroupByAccountNumber($row[ImportMapping::KEY_GROUP_ACCOUNT_NUMBER], $this->user->getHolding());
    }
}
