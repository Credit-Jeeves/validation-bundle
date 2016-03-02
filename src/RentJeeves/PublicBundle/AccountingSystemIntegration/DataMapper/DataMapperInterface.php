<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper;

use RentJeeves\PublicBundle\AccountingSystemIntegration\IntegratedModel;
use Symfony\Component\HttpFoundation\Request;

interface DataMapperInterface
{
    /**
     * @param Request $request
     * @return IntegratedModel
     */
    public function mapData(Request $request);
}