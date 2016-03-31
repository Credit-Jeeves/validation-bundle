<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper;

use RentJeeves\CoreBundle\Traits\ValidateEntities;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\PublicBundle\AccountingSystemIntegration\ASIIntegratedModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;

/**
 * DI\Service('accounting_system.integration.data_mapper.resman')
 */
class ResmanASIDataMapper implements ASIDataMapperInterface
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
     * @return ASIIntegratedModel
     */
    public function mapData(Request $request)
    {
        $ASIIntegratedModel = new ASIIntegratedModel(AccountingSystem::RESMAN);

        $ASIIntegratedModel->setResidentId($request->get('resid'));
        $ASIIntegratedModel->setLeaseId($request->get('leaseid'));
        $ASIIntegratedModel->setPropertyId($request->get('propid'));
        $ASIIntegratedModel->setUnitId($request->get('unitid'));
        $ASIIntegratedModel->setRent($request->get('rent'));
        $ASIIntegratedModel->setReturnUrl($request->get('redirect'));
        $ASIIntegratedModel->setAppFee($request->get('appfee'));
        $ASIIntegratedModel->setSecDep($request->get('secdep'));

        $ASIIntegratedModel->setReturnParams(['success' => 'true']);

        $amounts = [];
        if ($appFee = $ASIIntegratedModel->getAppFee()) {
            $amounts[DepositAccountType::APPLICATION_FEE] = $appFee;
        }
        if ($secDep = $ASIIntegratedModel->getSecDep()) {
            $amounts[DepositAccountType::SECURITY_DEPOSIT] = $secDep;
        }
        $ASIIntegratedModel->setAmounts($amounts);

        $this->validate($ASIIntegratedModel);
        if ($this->hasErrors()) {
            throw new \InvalidArgumentException(
                sprintf('Request has errors: %s', implode('; ', $this->getErrors()))
            );
        }

        return $ASIIntegratedModel;
    }
}
