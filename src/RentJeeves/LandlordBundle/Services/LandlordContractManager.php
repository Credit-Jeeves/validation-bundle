<?php

namespace RentJeeves\LandlordBundle\Services;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManager;
use RentJeeves\CheckoutBundle\Services\PaidFor;
use RentJeeves\CoreBundle\Serialization\Model\Contract as ModelContract;
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
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PaidFor
     */
    protected $paidFor;

    /**
     * @param TranslatorInterface $translator
     * @param ResidentManager $residentManager
     */
    public function __construct(
        TranslatorInterface $translator,
        ResidentManager $residentManager,
        EntityManager $em,
        PaidFor $paidFor
    ) {
        $this->translator = $translator;
        $this->residentManager = $residentManager;
        $this->em = $em;
        $this->paidFor = $paidFor;
    }

    /**
     * @param Contract $contract
     * @return ModelContract
     */
    public function mapContract(Contract $contractEntity)
    {
        $contract = new ModelContract($contractEntity);
        /** @var Operation $operation */
        $operation = $this->em->getRepository('DataBundle:Operation')->getLastOperation($contractEntity);
        if ($operation) {
            $contract->setLastPayment($operation->getOrder());
        }

        $contract->setRevokeMessage($this->getRevokeMessage($contractEntity));
        $contract->setPaidForArray($this->paidFor->getArray($contractEntity));

        return $contract;
    }

    /**
     * @param array $contracts
     * @param Group $currentGroup
     * @param Landlord $landlord
     * @return ModelContract[]
     */
    public function mapContracts(array $contracts)
    {
        $collection = [];
        /** @var Contract $contract */
        foreach ($contracts as $contract) {
            $collection[] = $this->mapContract($contract);
        }

        return $collection;
    }

    /**
     * @return string
     */
    public function getRevokeMessage(Contract $contract)
    {
        if ($contract->getStatus() === ContractStatus::INVITE &&
            $contract->getGroup()->getGroupSettings()->getIsIntegrated()
        ) {
            $hasMultipleContracts = $this->residentManager->hasMultipleContracts(
                $contract->getTenant(),
                $contract->getHolding()
            );
            $count = ($hasMultipleContracts) ? 1 : 0;

            return $this->translator->transChoice(
                'notice.revoke.residentId.multiple_contracts',
                $count
            );
        }

        return $this->translator->trans('revoke.inv.ask');
    }
}
