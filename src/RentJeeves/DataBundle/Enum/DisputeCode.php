<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class DisputeCode extends Enum
{
    const DISPUTE_CODE_BLANK = 'BLANK';

    /**
     * Account information disputed by consumer (Meets requirements of the Fair Credit Reporting Act)
     */
    const DISPUTE_CODE_XB = 'XB';

    /**
     * Completed investigation of FCRA dispute ‐ consumer disagrees
     */
    const DISPUTE_CODE_XC = 'XC';

    /**
     * Account previously in dispute ‐ now resolved, reported by data furnisher
     */
    const DISPUTE_CODE_XH = 'XH';

    /**
     * Removes the most recently reported Compliance Condition Code
     */
    const DISPUTE_CODE_XR = 'XR';
}
