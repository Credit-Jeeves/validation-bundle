<?php

namespace RentJeeves\LandlordBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Enum\ContractStatus;
use Symfony\Component\Translation\TranslatorInterface;
use RentJeeves\ComponentBundle\Service\ResidentManager;

class LandlordContractManager
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ResidentManager
     */
    protected $residentManager;

    /**
     * @param TranslatorInterface $translator
     * @param ResidentManager $residentManager
     */
    public function __construct(TranslatorInterface $translator, ResidentManager $residentManager)
    {
        $this->translator = $translator;
        $this->residentManager = $residentManager;
    }

    /**
     * @param array $contracts
     * @param Group $currentGroup
     * @param Landlord $landlord
     * @return array
     */
    public function convertContractsToArray(array $contracts, Group $currentGroup, Landlord $landlord)
    {
        $items = [];
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $item = $contract->getItem();
            if ($contract->getStatus() === ContractStatus::INVITE &&
                $currentGroup->getGroupSettings()->getIsIntegrated()
            ) {
                $hasMultipleContracts = $this->residentManager->hasMultipleContracts(
                    $contract->getTenant(),
                    $landlord->getHolding()
                );
                $count = ($hasMultipleContracts) ? 1 : 0;
                $item['revoke_message'] = $this->translator->transChoice(
                    'notice.revoke.residentId.multiple_contracts',
                    $count
                );
            } else {
                $item['revoke_message'] = $this->translator->trans('revoke.inv.ask');
            }
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param Group $group
     * @param mixed $contracts
     */
    public function convertContractsToGroupArray(Group $currentGroup, $contracts)
    {
        $items = [];
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            if ($contract->getGroup() === $currentGroup) {
                continue;
            }

            $items[] = [
                'groupName' => $contract->getGroup()->getName(),
                'groupId' => $contract->getGroup()->getId()
            ];
        }

        return $items;
    }
}
