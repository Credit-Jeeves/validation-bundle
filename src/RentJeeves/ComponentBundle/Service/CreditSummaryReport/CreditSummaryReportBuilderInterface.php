<?php

namespace RentJeeves\ComponentBundle\Service\CreditSummaryReport;

use CreditJeeves\DataBundle\Entity\User;

interface CreditSummaryReportBuilderInterface
{
    /**
     * @param User $user
     * @param bool $shouldUpdateReport
     */
    public function build(User $user, $shouldUpdateReport = false);
}
