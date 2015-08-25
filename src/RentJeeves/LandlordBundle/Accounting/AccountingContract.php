<?php

namespace RentJeeves\LandlordBundle\Accounting;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Entity\Contract;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("accounting.contract")
 */
class AccountingContract
{

    protected $em;

    /**
     * @InjectParams({
     *    "em" = @Inject("doctrine.orm.default_entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function moveContractToNewUser(Tenant $fromUser, Tenant $toUser)
    {
        if ($fromUser->getId() === $toUser->getId()) {
            return;
        }

        $contracts = $fromUser->getContracts();
        /**
         * @var $contractFrom Contract
         */
        foreach ($contracts as $contractFrom) {
            $contractTo = $this->em->getRepository('RjDataBundle:Contract')->getImportContract(
                $toUser->getId(),
                $contractFrom->getUnit()->getName(),
                null,
                $contractFrom->getProperty()->getId()
            );

            if ($contractTo) {
                $this->updateContract($contractTo, $contractFrom);
            } else {
                $contractFrom->setTenant($toUser);
                $contractNew = clone $contractFrom;
                $this->em->persist($contractNew);
            }
        }

        $residentsMapping = $fromUser->getResidentsMapping();

        /**
         * @var $resident ResidentMapping
         */
        foreach ($residentsMapping as $resident) {
            /**
             * @var $residentInDb ResidentMapping
             */
            $residentInDb = $this->em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
                array(
                    'tenant'     => $toUser->getId(),
                    'residentId' => $resident->getId(),
                    'holding'    => $resident->getHolding()->getId()
                )
            );

            if (!empty($residentInDb)) {
                continue;
            }
            $resident->setTenant($toUser);
            $residentNew = clone $resident;
            $this->em->persist($residentNew);
        }

        $this->em->remove($fromUser);
        $this->em->flush();
    }

    protected function updateContract(Contract $contractToUser, Contract $contractFromUser)
    {
        $contractToUser->setRent($contractFromUser->getRent());
        $contractToUser->setIntegratedBalance($contractFromUser->getIntegratedBalance());
        $contractToUser->setStartAt($contractFromUser->getStartAt());
        $contractToUser->setFinishAt($contractFromUser->getFinishAt());
    }
}
