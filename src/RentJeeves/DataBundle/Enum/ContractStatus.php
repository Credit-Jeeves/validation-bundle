<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * Link to discourse about this logic on email alexandr sharamko
 * https://mail.google.com/mail/u/0/?hl=RU#label/%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0%2Fforma+pro/14260742d42b950b
 *
 * Contract statuses
 * Avaliable chains:
 * 1. PENDING -> DELETED FROM DB
 * 2. PENDING -> APPROVED -> DELETED
 * 3. PENDING -> APPROVED -> CURRENT -> FINISHED
 * 4. INVITE -> DELETED FROM DB
 * 5. INVITE -> APPROVED -> DELETED
 * 6. INVITE -> APPROVED -> CURRENT -> FINISHED
 *
 * One notice if Landlord remove unit and this unit have contract
 * all this contract change status to FINISHED
 * This logic on src/RentJeeves/LandlordBundle/Controller/AjaxController.php
 * Method checkContractBeforeRemove
 *
 * @author Alex Emelyanov <alex.emelyanov.ua@gmail.com>
 */
class ContractStatus extends Enum
{
    /**
     * Contract was ordered by Tenant
     * @var string
     */
    const PENDING = 'pending';

    /**
     * Contract was oredred by Landlord
     * @var string
     */
    const INVITE = 'invite';

    /**
     * Contract was approved with another side
     * @var string
     */
    const APPROVED = 'approved';

    /**
     * Approved contract with payments
     * @var string
     */
    const CURRENT = 'current';

    /**
     * Contract was finished
     * @var string
     */
    const FINISHED = 'finished';

    /**
     * Contract was deleted
     * @var string
     */
    const DELETED = 'deleted';

    /**
     * Returns an array of statuses with currentStatus as a first value
     *
     * @param $currentStatus
     * @return array
     */
    public static function getStatuses($currentStatus)
    {
        $currentStatus = [$currentStatus => $currentStatus];
        $otherStatuses = array_diff(
            ContractStatus::all(),
            $currentStatus
        );
        $result = [];
        foreach (($currentStatus + $otherStatuses) as $key => $value) {
            $result[$value]= $value;
        }
        return $result;
    }
}
