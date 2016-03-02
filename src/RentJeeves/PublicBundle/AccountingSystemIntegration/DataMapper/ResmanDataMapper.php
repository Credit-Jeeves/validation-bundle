<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper;

use RentJeeves\CoreBundle\Traits\ValidateEntities;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\PublicBundle\AccountingSystemIntegration\IntegratedModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;

/**
 * DI\Service('accounting_system.integration.data_mapper.resman')
 */
class ResmanDataMapper implements DataMapperInterface
{
    use ValidateEntities;

    /**
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @return IntegratedModel
     */
    public function mapData(Request $request)
    {
        $integratedModel = new IntegratedModel(AccountingSystem::RESMAN);

        $integratedModel->setResidentId($request->get('resid'));
        $integratedModel->setLeaseId($request->get('leaseid'));
        $integratedModel->setPropertyId($request->get('propid'));
        $integratedModel->setUnitId($request->get('unitid'));
        $integratedModel->setRent($request->get('rent'));
        $integratedModel->setReturnUrl($request->get('redirect'));
        $integratedModel->setAppFee($request->get('appfee'));
        $integratedModel->setSecDep($request->get('secdep'));

        $integratedModel->setReturnParams(['success' => 'true']);

        $amounts = [];
        if ($appFee = $integratedModel->getAppFee()) {
            $amounts[DepositAccountType::APPLICATION_FEE] = $appFee;
        }
        if ($secDep = $integratedModel->getSecDep()) {
            $amounts[DepositAccountType::SECURITY_DEPOSIT] = $secDep;
        }
        $integratedModel->setAmounts($amounts);

        $this->validate($integratedModel);
        if ($this->hasErrors()) {
            throw new \InvalidArgumentException(
                sprintf('Request has errors: %s', implode('; ', $this->getErrors()))
            );
        }

        return $integratedModel;
    }
}
