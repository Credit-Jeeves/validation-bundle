<?php

namespace RentJeeves\LandlordBundle\Accounting\Import\EntityManager;

use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\LandlordBundle\Accounting\Import\Mapping\MappingAbstract as Mapping;
use RentJeeves\LandlordBundle\Model\Import;

/**
 * @property Import currentImportModel
 */
trait Resident
{
    /**
     * @var array
     */
    protected $usedResidentsIds = [];

    /**
     * @var array
     */
    protected $usedEmails = [];

    /**
     * @param string $residentId
     */
    protected function addResidentId($residentId)
    {
        $email = $this->currentImportModel->getTenant()->getEmail();
        // note that we have seen this residentId before
        if (!isset($this->usedResidentsIds[$residentId])) {
            $this->usedResidentsIds[$residentId] = 1;
        }

        // note that we have seen email before for this residentId
        if (!empty($email) && !isset($this->usedEmails[$email])) {
            $this->usedEmails[$email] = $residentId;
        }

        // note that we have seen this residentId for more than one email
        if (!empty($email) && $residentId !== $this->usedEmails[$email]) {
            $this->usedResidentsIds[$residentId]++;
            $this->usedEmails[$email] = $residentId;
        }
    }

    protected function getEmailByResident($residentId)
    {
        return array_search($residentId, $this->usedEmails);
    }

    protected function clearResidentData()
    {
        $this->usedResidentsIds = [];
        $this->usedEmails = [];
    }

    /**
     * @return bool
     */
    public function isUsedResidentId()
    {
        $residentMapping = $this->currentImportModel->getResidentMapping();
        $id = $residentMapping->getResidentId();

        return (isset($this->usedResidentsIds[$id]) && $this->usedResidentsIds[$id] > 1) ? true : false;
    }

    /**
     * @param array $row
     *
     */
    public function setResident(array $row)
    {
        if (is_null($this->currentImportModel->getTenant()->getId())) {
            $residentMapping = $this->createNewResidentMapping($row);

            return;
        }
        /** @var ResidentMapping $residentMapping */
        $residentMapping = $this->em->getRepository('RjDataBundle:ResidentMapping')->findOneBy(
            array(
                'tenant' => $this->currentImportModel->getTenant()->getId(),
                'holding' => $this->user->getHolding()->getId(),
                'residentId' => $row[Mapping::KEY_RESIDENT_ID],
            )
        );

        if (empty($residentMapping)) {
            $residentMapping = $this->createNewResidentMapping($row);
        } else {
            $this->addResidentId($row[Mapping::KEY_RESIDENT_ID]);
        }

        $this->currentImportModel->setResidentMapping($residentMapping);
    }

    public function createNewResidentMapping(array $row)
    {
        $residentMapping = new ResidentMapping();
        $residentMapping->setTenant($this->currentImportModel->getTenant());
        $residentMapping->setHolding($this->user->getHolding());
        $residentMapping->setResidentId($row[Mapping::KEY_RESIDENT_ID]);

        $this->currentImportModel->setResidentMapping($residentMapping);
        $this->addResidentId($row[Mapping::KEY_RESIDENT_ID]);

        return $residentMapping;
    }
}
