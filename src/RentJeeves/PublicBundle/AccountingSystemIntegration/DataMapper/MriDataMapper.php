<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper;

use RentJeeves\CoreBundle\Services\HMACGenerator;
use RentJeeves\CoreBundle\Traits\ValidateEntities;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\PublicBundle\AccountingSystemIntegration\IntegratedModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;

/**
 * DI\Service('accounting_system.integration.data_mapper.mri')
 */
class MriDataMapper implements DataMapperInterface
{
    use ValidateEntities;

    /**
     * @var HMACGenerator
     */
    protected $HMACGenerator;

    /**
     * @param Validator $validator
     * @param HMACGenerator $HMACGenerator
     */
    public function __construct(Validator $validator, HMACGenerator $HMACGenerator)
    {
        $this->validator = $validator;
        $this->HMACGenerator = $HMACGenerator;
    }

    /**
     * @param Request $request
     * @return IntegratedModel
     */
    public function mapData(Request $request)
    {
        $this->checkDigest($request);
        $integratedModel = new IntegratedModel(AccountingSystem::MRI);

        $integratedModel->setResidentId($request->get('resid'));
        $integratedModel->setLeaseId($request->get('leaseid'));
        $integratedModel->setPropertyId($request->get('propid'));
        $integratedModel->setBuildingId($request->get('buildingid'));
        $integratedModel->setUnitId($request->get('unitid'));

        $integratedModel->setRent($request->get('rent'));
        $integratedModel->setReturnUrl($request->get('ReturnUrl'));
        $integratedModel->setAppFee((float) $request->get('appfee'));
        $integratedModel->setSecDep((float) $request->get('secdep'));

        $integratedModel->setHoldingId($request->get('holdingid'));
        $integratedModel->setTrackingId($request->get('trackingid'));
        $integratedModel->setUserCancelUrl($request->get('UserCancelUrl'));

        $amounts = [];
        $sum = 0;
        if ($appFee = $integratedModel->getAppFee()) {
            $amounts[DepositAccountType::APPLICATION_FEE] = $appFee;
            $sum += $appFee;
        }
        if ($secDep = $integratedModel->getSecDep()) {
            $amounts[DepositAccountType::SECURITY_DEPOSIT] = $secDep;
            $sum += $appFee;
        }
        $integratedModel->setAmounts($amounts);
        $integratedModel->setSum($sum);
        $integratedModel->setReturnParams($this->prepareReturnParams($integratedModel));
        $integratedModel->setReturnMethod('post');

        $this->validate($integratedModel, ['Default', 'mri']);
        if ($this->hasErrors()) {
            throw new \InvalidArgumentException(
                sprintf('Request has errors: %s', implode('; ', $this->getErrors()))
            );
        }

        return $integratedModel;
    }

    /**
     * @param Request $request
     * @throws \InvalidArgumentException
     */
    protected function checkDigest(Request $request)
    {
        if (!$this->HMACGenerator->validateHMAC($request->request->all())) {
            throw new \InvalidArgumentException('Digest is invalid');
        }
    }

    /**
     * @param IntegratedModel $integratedModel
     * @return array
     */
    protected function prepareReturnParams(IntegratedModel $integratedModel)
    {
        $returnParams = [
            'trackingid' => $integratedModel->getTrackingId(),
            'apipost' => 'true',
            'sum' => number_format($integratedModel->getSum(), 2, '.', ''),

        ];
        $returnParams['Digest'] = $this->HMACGenerator->generateHMAC($returnParams);

        return $returnParams;
    }
}
