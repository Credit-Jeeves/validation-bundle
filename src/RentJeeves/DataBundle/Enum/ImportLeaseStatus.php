<?php
namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

/**
 * @deprecated should be removed in near future.
 */
class ImportLeaseStatus extends Enum
{
    const NEWONE = 'new';

    const MATCH = 'match';

    const ERROR = 'error';

    /**
     * {@inheritdoc}
     */
    public static function isValid($value)
    {
        return is_null($value) || parent::isValid($value);
    }
}
