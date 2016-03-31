<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper;

use RentJeeves\PublicBundle\AccountingSystemIntegration\ASIIntegratedModel;
use Symfony\Component\HttpFoundation\Request;

interface ASIDataMapperInterface
{
    /**
     * @param Request $request
     * @return ASIIntegratedModel
     */
    public function mapData(Request $request);
}
