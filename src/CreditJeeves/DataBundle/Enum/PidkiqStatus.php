<?php

namespace CreditJeeves\DataBundle\Enum;

use CreditJeeves\CoreBundle\Enum;

class PidkiqStatus extends Enum
{
    const SUCCESS = 'success';

    const INPROGRESS = 'inprogress';

    const FAILURE = 'failure';

    const LOCKED = 'locked';

    const BACKOFF = 'backoff';

    const UNABLE = 'unable';
}
