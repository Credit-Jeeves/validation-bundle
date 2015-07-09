<?php

namespace RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use RentJeeves\CoreBundle\Services\GeoCoder;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException;

/**
 * @method Group map(array $data, Group $group = null)
 */
class GroupMapper extends AbstractMapper
{
    /**
     * @var GeoCoder
     */
    protected $geoCoder;

    /**
     * @param GeoCoder $geoCoder
     */
    public function __construct(GeoCoder $geoCoder)
    {
        $this->geoCoder = $geoCoder;
    }

    /**
     * @throws MappingException
     *
     * @return \CreditJeeves\DataBundle\Entity\Group
     */
    protected function mapObject()
    {
        $externalGroupId = $this->get('login_id');
        if (false != $group = $this->getGroupRepository()->findOneBy(['externalGroupId' => $externalGroupId])) {
            return $group;
        }

        if (false === $this->isValidAddress()) {
            throw new MappingException(sprintf(
                '[Mapping] : Address (%s) is not found by google', $this->getAddress()
            ));
        }

        return $this->createGroup();
    }

    /**
     * @return Group
     */
    protected function createGroup()
    {
        $newGroup = new Group();

        $companyName = $this->get('company_name');
        if (empty($companyName) === true) {
            $companyName = sprintf('%s %s', $this->get('first_name'), $this->get('last_name'));
        }

        $newGroup->setName($companyName);
        $newGroup->setMailingAddressName($companyName);
        $newGroup->setStreetAddress1($this->get('ll_address'));
        $newGroup->setStreetAddress2($this->get('ll_unit'));
        $newGroup->setCity($this->get('ll_city'));
        $newGroup->setState($this->get('ll_state'));
        $newGroup->setZip($this->get('ll_zipcode'));
        $newGroup->setHolding($this->createHolding());
        $newGroup->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $newGroup->setExternalGroupId($this->get('login_id'));

        $this->em->persist($newGroup);

        $this->createGroupSetting($newGroup);

        return $newGroup;
    }

    /**
     * @return Holding
     */
    protected function createHolding()
    {
        $newHolding = new Holding();
        $newHolding->setName($this->get('ll_email'));

        $this->em->persist($newHolding);

        return $newHolding;
    }

    /**
     * @param Group $group
     *
     * @return GroupSettings
     */
    protected function createGroupSetting(Group $group)
    {
        $newGroupSettings = $group->getGroupSettings();
        /** @TODO: change type after merge PR */
        $newGroupSettings->setPaymentProcessor(PaymentProcessor::ACI_COLLECT_PAY);

        $this->em->persist($newGroupSettings);

        return $newGroupSettings;
    }

    /**
     * @return string
     */
    protected function getAddress()
    {
        return sprintf(
            '%s, %s, %s, %s',
            $this->get('ll_address'),
            $this->get('ll_city'),
            $this->get('ll_state'),
            $this->get('ll_zipcode')
        );
    }

    /**
     * @return boolean
     */
    protected function isValidAddress()
    {
        return $this->geoCoder->getGoogleGeocode($this->getAddress()) === false ? false : true;
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\GroupRepository
     */
    protected function getGroupRepository()
    {
        return $this->em->getRepository('DataBundle:Group');
    }
}
