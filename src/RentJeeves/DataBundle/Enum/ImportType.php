<?php

namespace RentJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class ImportType extends Enum
{
    const SINGLE_PROPERTY = 'single_property';

    const MULTI_PROPERTIES = 'multi_properties';

    const MULTI_GROUPS = 'multi_groups';
}
