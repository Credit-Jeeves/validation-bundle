<?php

namespace RentJeeves\TrustedLandlordBundle\Command;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\Query;
use RentJeeves\CoreBundle\Command\BaseCommand;
use RentJeeves\CoreBundle\Services\AddressLookup\Model\Address;
use RentJeeves\DataBundle\Entity\CheckMailingAddress;
use RentJeeves\DataBundle\Entity\Job;
use RentJeeves\DataBundle\Entity\TrustedLandlord;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\TrustedLandlordStatus;
use RentJeeves\DataBundle\Enum\TrustedLandlordType;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MoveDTRGroupAddressToCheckMailingAddressCommand extends BaseCommand
{
    const BATCH_SIZE = 20;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('renttrack:group:move-mailing-address')
            ->addOption(
                'groups-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Group id seperated by comma. Example 1,2,3,4,5'
            )
            ->setDescription('Move mailing address from Group to CheckMailingAddress and create TrustedLandlord');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupsId = $input->getOption('groups-id');

        if (!empty($groupsId)) {
            return $this->moveGroupsAddressToCheckMailingAddress(explode(',', $groupsId));
        }
        $query = "SELECT g FROM CreditJeeves\DataBundle\Entity\Group as g WHERE g.orderAlgorithm='%s' AND";
        $query .= " g.trustedLandlord IS NULL";

        $iterableResult = $this
            ->getEntityManager()
            ->createQuery(sprintf($query, OrderAlgorithmType::PAYDIRECT))
            ->iterate(null, Query::HYDRATE_ARRAY);
        $groupsId = [];

        /** @var Group $group */
        while ((list($group) = $iterableResult->next()) !== false) {
            $groupsId[] = $group['id'];

            if (count($groupsId) === self::BATCH_SIZE) {
                $this->createJob($groupsId);
                $groupsId = [];
            }
        }

        if (!empty($groupsId)) {
            $this->createJob($groupsId);
        }
    }

    /**
     * @param $groupsId
     */
    protected function createJob($groupsId)
    {
        $command = 'renttrack:group:move-mailing-address';
        $parameter = '--groups-id=' . implode(',', $groupsId) ;
        $job = new Job($command, [$parameter]);
        $this->getEntityManager()->persist($job);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        $this->getLogger()->info(sprintf('Created command %s parameter %s', $command, $parameter));
    }

    /**
     * @param array $groupsId
     */
    protected function moveGroupsAddressToCheckMailingAddress(array $groupsId)
    {
        $hadErrors = false;
        $this->getLogger()->info(
            sprintf('Start moving DTR groups (%s) mailing address to CheckMailingAddress...', implode(',', $groupsId))
        );
        $em = $this->getEntityManager();
        /** @var $group \CreditJeeves\DataBundle\Entity\Group */
        foreach ($groupsId as $groupId) {
            $group = $em->getRepository('DataBundle:Group')->find($groupId);
            if ($group === null) {
                $this->getLogger()->error('Group ID not found: ' . $groupId);
                $hadErrors = true;
                continue;
            }

            $this->getLogger()->info(sprintf('Moving address for Group #%d "%s":', $group->getId(), $group->getName()));
            if (null !== $group->getTrustedLandlord()) {
                $this->getLogger()->info(sprintf(
                    'Group #%d "%s" already has TrustedLandlord id #%d -- skipping',
                    $group->getId(),
                    $group->getName(),
                    $group->getTrustedLandlord()->getId()
                ));

                continue;
            }

            try {
                $standardizedAddress = $this->getLookupService()->lookup(
                    $group->getStreetAddress1() . ' ' . $group->getStreetAddress2(),
                    $group->getCity(),
                    $group->getState(),
                    $group->getZip()
                );

                if (!$this->isUniqueIndex($standardizedAddress->getIndex())) {
                    throw new \Exception(sprintf(
                        'CheckMailingAddress with index "%s" already exists',
                        $standardizedAddress->getIndex()
                    ));
                }

                $mailingAddress = $this->createCheckMailingAddress($group, $standardizedAddress);
                $trustedLandlord = $this->createTrustedLandlord($group, $mailingAddress);
                $group->setTrustedLandlord($trustedLandlord);

                $em->persist($trustedLandlord);
                $em->flush();

                $this->getLogger()->info(sprintf(
                    'Address for Group #%d "%s" has been successfully moved',
                    $group->getId(),
                    $group->getName()
                ));
            } catch (\Exception $e) {
                $this->getLogger()->error(sprintf('Error occurred: %s', $e->getMessage()));

                continue;
            }
        }

        $this->getLogger()->info(sprintf('DTR groups(%s) have been processed.', implode(',', $groupsId)));

        $returnValue = ($hadErrors) ? 1 : 0;
        return $returnValue;
    }

    /**
     * @return \RentJeeves\CoreBundle\Services\AddressLookup\SmartyStreetsAddressLookupService
     */
    protected function getLookupService()
    {
        return $this->getContainer()->get('address_lookup_service');
    }

    /**
     * @param Group $group
     * @param Address $address
     * @return CheckMailingAddress
     */
    protected function createCheckMailingAddress(Group $group, Address $address)
    {
        $mailingAddress = new CheckMailingAddress();
        $mailingAddress->setAddressee($group->getMailingAddressName());
        $mailingAddress->setAddress1($group->getStreetAddress1());
        $mailingAddress->setAddress2($group->getStreetAddress2());
        $mailingAddress->setState($group->getState());
        $mailingAddress->setCity($group->getCity());

        $mailingAddress->setZip($group->getZip());
        $mailingAddress->setExternalLocationId($group->getExternalGroupId());
        $mailingAddress->setIndex($address->getIndex());
        $mailingAddress->setUpdatedAt($group->getUpdatedAt());
        $mailingAddress->setCreatedAt($group->getCreatedAt());

        return $mailingAddress;
    }

    /**
     * @param Group $group
     * @param CheckMailingAddress $mailingAddress
     * @return TrustedLandlord
     */
    protected function createTrustedLandlord(Group $group, CheckMailingAddress $mailingAddress)
    {
        $trustedLandlord = new TrustedLandlord();
        $trustedLandlord->setCheckMailingAddress($mailingAddress);
        $trustedLandlord->setCompanyName($group->getName());
        $trustedLandlord->setType(TrustedLandlordType::COMPANY);
        $trustedLandlord->setPhone($group->getPhone());
        $trustedLandlord->setStatus(TrustedLandlordStatus::TRUSTED);

        return $trustedLandlord;
    }

    /**
     * @param string $index
     * @return bool
     */
    protected function isUniqueIndex($index)
    {
        $address = $this->getEntityManager()->getRepository('RjDataBundle:CheckMailingAddress')->findOneByIndex($index);

        return null === $address;
    }
}
