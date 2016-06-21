<?php

namespace RentJeeves\PublicBundle\Tests\Functional;

use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\TestBundle\Functional\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;

class ASIDataManagerCase extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldSuccessPrepareReturnParamsForMri()
    {
        $requestParams = [
            'resid' => 'res_1',
            'leaseid' => 'lease_1',
            'propid' => 'prop_1',
            'holdingid' => '5',
            'trackingid' => 1112,
            'Digest' => 'dd64b62279ea4e1e81abedb44e8e98f8251874ff1806e1c364b619cf7cffa173'
        ];
        $request = new Request([], $requestParams);

        $integrationDataManager = $this->getContainer()->get('accounting_system.integration.data_manager');

        $integrationDataManager->processRequestData(AccountingSystem::MRI, $request);
        $integrationDataManager->setPaidPayment(DepositAccountType::APPLICATION_FEE, 120.00);
        $integrationDataManager->setPaidPayment(DepositAccountType::SECURITY_DEPOSIT, 220.00);

        // should reinit service for get data from session and deserialized
        $this->getContainer()->set('accounting_system.integration.data_manager', null);
        $integrationDataManager = $this->getContainer()->get('accounting_system.integration.data_manager');

        $this->assertEquals(
            [
                'trackingid' => 1112,
                'apipost' => 'true',
                'sum' => '340.00',
                'Digest' => 'f88e289c4d9b9cc33f8b8007e7898f3dff4316c6e8049009f869f878337e49bd'
            ],
            $integrationDataManager->getReturnParams(),
            'Prepared return parameters is invalid'
        );
    }
}
