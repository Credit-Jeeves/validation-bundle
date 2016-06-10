<?php

namespace RentJeeves\ImportBundle\LeaseImport\Transformer;

use CreditJeeves\DataBundle\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Import;
use RentJeeves\DataBundle\Entity\ImportLease;
use RentJeeves\DataBundle\Enum\ImportLeaseResidentStatus;
use RentJeeves\ExternalApiBundle\Model\ResMan\Customer;
use RentJeeves\ExternalApiBundle\Model\ResMan\RtCustomer;

/**
 * Service`s name "import.lease.transformer.resman" (public = false)
 */
class ResmanTransformer implements TransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityManagerInterface $em
     * @param LoggerInterface        $logger
     */
    final public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function transformData(array $accountingSystemData, Import $import)
    {
        /**
         * @var RtCustomer $baseCustomer
         */
        foreach ($accountingSystemData as $baseCustomer) {
            if (count($baseCustomer->getCustomers()->getCustomer()) === 0) {
                continue;
            }
            foreach ($baseCustomer->getCustomers()->getCustomer() as $currentCustomer) {
                $importLease = new ImportLease();
                $importLease->setImport($import);
                $importLease->setExternalResidentId($this->getExternalResidentId($baseCustomer, $currentCustomer));
                $importLease->setTenantEmail($this->getTenantEmail($baseCustomer, $currentCustomer));
                $importLease->setFirstName($this->getFirstName($baseCustomer, $currentCustomer));
                $importLease->setLastName($this->getLastName($baseCustomer, $currentCustomer));
                $importLease->setPhone($this->getPhone($baseCustomer, $currentCustomer));
                $importLease->setDateOfBirth($this->getDateOfBirth($baseCustomer, $currentCustomer));
                $importLease->setExternalPropertyId($this->getExternalPropertyId($baseCustomer, $currentCustomer));
                $importLease->setExternalBuildingId($this->getExternalBuildingId($baseCustomer, $currentCustomer));
                $importLease->setExternalUnitId($this->getExternalUnitId($baseCustomer, $currentCustomer));
                $importLease->setExternalLeaseId($this->getExternalLeaseId($baseCustomer, $currentCustomer));
                $importLease->setResidentStatus($this->getResidentStatus($baseCustomer, $currentCustomer));
                $importLease->setPaymentAccepted($this->getPaymentAccepted($baseCustomer, $currentCustomer));
                $importLease->setDueDate($this->getDueDate($import->getGroup()));
                $importLease->setRent($this->getRent($baseCustomer, $currentCustomer));
                $importLease->setIntegratedBalance($this->getIntegratedBalance($baseCustomer, $currentCustomer));
                $importLease->setStartAt($this->getStartAt($baseCustomer, $currentCustomer));
                $importLease->setFinishAt($this->getFinishAt($baseCustomer, $currentCustomer));

                $this->em->persist($importLease);
                $this->em->flush();
            }
        }
        $this->logger->info(
            sprintf(
                'Finished process transformData for Import#%d',
                $import->getId()
            ),
            ['group' => $import->getGroup()]
        );
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getExternalResidentId(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $currentCustomer->getCustomerId();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getTenantEmail(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $currentCustomer->getAddress()->getEmail();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getFirstName(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $currentCustomer->getUserName()->getFirstName();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getLastName(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $currentCustomer->getUserName()->getLastName();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getPhone(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return null;
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getDateOfBirth(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return null;
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getExternalPropertyId(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $baseCustomer->getRtUnit()->getUnit()->getPropertyPrimaryID();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getExternalBuildingId(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $baseCustomer->getRtUnit()->getUnit()->getInformation()->getBuildingID();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getExternalUnitId(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $baseCustomer->getRtUnit()->getUnitId();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getExternalLeaseId(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $baseCustomer->getCustomerId();
    }

    /**
     * @TODO: PLS check it
     *
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getResidentStatus(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return ImportLeaseResidentStatus::CURRENT;
//        return $currentCustomer->getType();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getPaymentAccepted(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $baseCustomer->getRentTrackPaymentAccepted();
    }

    /**
     * @param Group $group
     *
     * @return int
     */
    protected function getDueDate(Group $group)
    {
        return $group->getGroupSettings()->getDueDate();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getRent(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $currentCustomer->getLease()->getCurrentRent();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return string
     */
    protected function getIntegratedBalance(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $baseCustomer->getRentTrackBalance();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return \DateTime
     */
    protected function getStartAt(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $currentCustomer->getLease()->getLeaseFromDate();
    }

    /**
     * @param RtCustomer $baseCustomer
     * @param Customer   $currentCustomer
     *
     * @return \DateTime
     */
    protected function getFinishAt(RtCustomer $baseCustomer, Customer $currentCustomer)
    {
        return $currentCustomer->getLease()->getLeaseToDate();
    }
}
