<?php

namespace RentJeeves\LandlordBundle\Form\Enum;

use CreditJeeves\CoreBundle\Enum;

class ImportType extends Enum
{
    const SINGLE_PROPERTY = 'single_property';

    const MULTI_PROPERTY = 'multi_property';

    const MULTI_GROUP = 'multi_group';
}
