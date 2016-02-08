<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PaymentProcessor extends Enum
{
    const HEARTLAND = 'heartland';

    const ACI = 'aci';

    const PROFIT_STARS = 'profit_stars';

    /**
     * @param array $values
     * @return array
     */
    public static function cacheSpecificTitles(array $values)
    {
        $specificTitles = [];
        $titles = self::cachedTitles();
        foreach ($titles as $titleKey => $titleValue) {
            if (in_array($titleKey, $values)) {
                $specificTitles[$titleKey] = $titleValue;
            }
        }

        return $specificTitles;
    }
}
