<?php
namespace RentJeeves\CoreBundle\Services;

use CreditJeeves\DataBundle\Enum\UserIsVerified;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\DataBundle\Entity\Contract;
use DateTime;

/**
 * @author Alexandr Sharamko <alexandr.sharamko@gmail.com>
 *
 * @Service("contract.trans_union_reporting")
 */
class TUReporting
{
    // It's day when Terms and Service was changed
    // see task https://credit.atlassian.net/browse/RT-747
    const DATE_CHANGE_TERMS_AND_SERVICE = '09/09/2014';

    /**
     * @param Contract $contract
     * @return Contract
     * @throws \Exception
     */
    public function turnOnTransUnionReporting(Contract $contract)
    {
        $tenant = $contract->getTenant();
        if (empty($tenant)) {
            return;
        }
        
        if ($tenant->getCreatedAt() < $this->getDate()) {
            return;
        }

        if (!$contract->getReportToTransUnion() &&
            $tenant->getIsVerified() ===  UserIsVerified::PASSED &&
            !$contract->getGroup()->getGroupSettings()->getIsReportingOff()
        ) {
            $contract->setTransUnionStartAt(new DateTime());
            $contract->setReportToTransUnion(true);
        }

        return;
    }

    protected function getDate()
    {
        return DateTime::createFromFormat('d/m/Y', self::DATE_CHANGE_TERMS_AND_SERVICE);
    }
}
