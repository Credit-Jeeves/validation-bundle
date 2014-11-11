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
     * Therefore,
     * when someone passes full PID/KIQ,
     * and they joined on 9/9/2014 or later,
     * and that contract is not on a group that has reporting disabled
     *
     * @param Contract $contract
     * @return boolean
     */
    public function turnOnTransUnionReporting(Contract $contract)
    {
        $tenant = $contract->getTenant();
        if (empty($tenant)) {
            return false;
        }

        if ($tenant->getCreatedAt() <= $this->getDate()) {
            return false;
        }

        if (!$contract->getReportToTransUnion() &&
            $tenant->getIsVerified() === UserIsVerified::PASSED &&
            !$contract->getGroup()->getGroupSettings()->getIsReportingOff()
        ) {
            $contract->setTransUnionStartAt(new DateTime());
            $contract->setReportToTransUnion(true);
            return true;
        }

        return false;
    }

    protected function getDate()
    {
        return DateTime::createFromFormat('d/m/Y', self::DATE_CHANGE_TERMS_AND_SERVICE);
    }
}
