<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ImportPropertyStatus extends Enum
{
    const NONE = 'none';

    const ERROR = 'error';

    const NEW_UNIT = 'new_unit';

    const NEW_PROPERTY_AND_UNIT = 'new_property_and_unit';

    const MATCH = 'match';
}
