<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper;

use RentJeeves\CoreBundle\Services\HMACGenerator;
use RentJeeves\CoreBundle\Traits\ValidateEntities;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\PublicBundle\AccountingSystemIntegration\ASIIntegratedModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;
use Psr\Log\LoggerInterface;

/**
 * DI\Service('accounting_system.integration.data_mapper.mri')
 */
class MriASIDataMapper implements ASIDataMapperInterface
{
    use ValidateEntities;

    /**
     * @var HMACGenerator
     */
    protected $HMACGenerator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Validator $validator
     * @param HMACGenerator $HMACGenerator
     */
    public function __construct(Validator $validator, HMACGenerator $HMACGenerator, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->HMACGenerator = $HMACGenerator;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @return ASIIntegratedModel
     */
    public function mapData(Request $request)
    {
        $this->checkDigest($request);
        $integratedModel = new ASIIntegratedModel(AccountingSystem::MRI);

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
        if ($appFee = $integratedModel->getAppFee()) {
            $amounts[DepositAccountType::APPLICATION_FEE] = $appFee;
        }
        if ($secDep = $integratedModel->getSecDep()) {
            $amounts[DepositAccountType::SECURITY_DEPOSIT] = $secDep;
        }
        $integratedModel->setAmounts($amounts);
        $integratedModel->setReturnMethod('post');

        $this->validate($integratedModel, ['Default', 'mri']);
        if ($this->hasErrors()) {
            $message = sprintf('MRI Resident Connect: Request has errors: %s', implode('; ', $this->getErrors()));
            $this->logger->alert($message);
            throw new \InvalidArgumentException($message);
        }

        return $integratedModel;
    }

    /**
     * @param ASIIntegratedModel $integratedModel
     * @return array
     */
    public function prepareReturnParams(ASIIntegratedModel $integratedModel)
    {
        $returnParams = [
            'trackingid' => $integratedModel->getTrackingId(),
            'apipost' => 'true',
            'sum' => number_format(array_sum($integratedModel->getPaidAmounts()), 2, '.', ''),

        ];
        $returnParams['Digest'] = $this->HMACGenerator->generateHMAC($returnParams);

        return $returnParams;
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
}
