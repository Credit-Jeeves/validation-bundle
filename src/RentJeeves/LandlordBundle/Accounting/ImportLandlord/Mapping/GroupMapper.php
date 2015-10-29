<?php

namespace RentJeeves\LandlordBundle\Accounting\ImportLandlord\Mapping;

use CreditJeeves\DataBundle\Entity\Group;
use CreditJeeves\DataBundle\Entity\Holding;
use CreditJeeves\DataBundle\Enum\GroupType;
use RentJeeves\CoreBundle\Services\AddressLookup\AddressLookupInterface;
use RentJeeves\CoreBundle\Services\AddressLookup\Exception\AddressLookupException;
use RentJeeves\DataBundle\Entity\DepositAccount;
use RentJeeves\DataBundle\Entity\GroupSettings;
use RentJeeves\DataBundle\Enum\DepositAccountStatus;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\DataBundle\Enum\OrderAlgorithmType;
use RentJeeves\DataBundle\Enum\PaymentProcessor;
use RentJeeves\LandlordBundle\Accounting\ImportLandlord\Exception\MappingException;

/**
 * @method Group map(array $data, Group $group = null)
 */
class GroupMapper extends AbstractMapper
{
    /**
     * @var AddressLookupInterface
     */
    protected $addressLookupService;

    /**
     * @see parameter paydirect_fee_cc
     * @var float
     */
    protected $defaultFeeCC;

    /**
     * @see parameter paydirect_fee_cc
     * @var float
     */
    protected $defaultFeeACH;

    /**
     * @see parameter aci.collect_pay.business_id
     * @var string
     */
    protected $defaultDivisionId;

    /**
     * @param AddressLookupInterface $addressLookupService
     * @param array $defaultParams
     */
    public function __construct(AddressLookupInterface $addressLookupService, array $defaultParams = [])
    {
        $this->addressLookupService = $addressLookupService;

        $this->defaultFeeCC = isset($defaultParams['fee_cc']) ? $defaultParams['fee_cc'] : 0.0;
        $this->defaultFeeACH = isset($defaultParams['fee_ach']) ? $defaultParams['fee_ach'] : 0.0;
        $this->defaultDivisionId = isset($defaultParams['division_id']) ? $defaultParams['division_id'] : null;
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
            throw new MappingException(
                sprintf(
                    '[Mapping] : Address (%s, %s, %s, %s) is not found by AddressLookupService',
                    $this->get('ll_address'),
                    $this->get('ll_city'),
                    $this->get('ll_state'),
                    $this->get('ll_zipcode')
                )
            );
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
        $newGroup->setType(GroupType::RENT);
        $newGroup->setOrderAlgorithm(OrderAlgorithmType::PAYDIRECT);
        $newGroup->setExternalGroupId($this->get('login_id'));

        $this->createGroupSetting($newGroup);
        $this->createDepositAccount($newGroup);

        return $newGroup;
    }

    /**
     * @return Holding
     */
    protected function createHolding()
    {
        $newHolding = new Holding();
        $newHolding->setName($this->getEmail());

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
        $newGroupSettings->setPaymentProcessor(PaymentProcessor::ACI);
        $newGroupSettings->setAutoApproveContracts(true);
        $newGroupSettings->setPassedAch(true);
        $newGroupSettings->setFeeCC($this->defaultFeeCC);
        $newGroupSettings->setFeeACH($this->defaultFeeACH);

        return $newGroupSettings;
    }

    /**
     * @param Group $group
     *
     * @return DepositAccount
     */
    protected function createDepositAccount(Group $group)
    {
        $newDepositAccount = new DepositAccount($group);
        $newDepositAccount->setType(DepositAccountType::RENT);
        $newDepositAccount->setMerchantName($this->defaultDivisionId);
        if ($this->defaultDivisionId) {
            $newDepositAccount->setStatus(DepositAccountStatus::DA_COMPLETE);
        }
        $newDepositAccount->setPaymentProcessor(PaymentProcessor::ACI);

        $group->addDepositAccount($newDepositAccount);

        return $newDepositAccount;
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
        try {
            $this->addressLookupService->lookup(
                $this->get('ll_address'),
                $this->get('ll_city'),
                $this->get('ll_state'),
                $this->get('ll_zipcode')
            );

            return true;
        } catch (AddressLookupException $e) {
            return false;
        }
    }

    /**
     * @return \CreditJeeves\DataBundle\Entity\GroupRepository
     */
    protected function getGroupRepository()
    {
        return $this->em->getRepository('DataBundle:Group');
    }
}
