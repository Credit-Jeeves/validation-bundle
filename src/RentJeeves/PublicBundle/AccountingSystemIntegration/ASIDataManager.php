<?php

namespace RentJeeves\PublicBundle\AccountingSystemIntegration;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use RentJeeves\PublicBundle\AccountingSystemIntegration\DataMapper\ASIDataMapperFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * DI\Service('accounting_system.integration.data_manager')
 */
class ASIDataManager
{
    const SESSION_INTEGRATION_DATA = 'integration_data';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ASIIntegratedModel
     */
    protected $cachedData;

    /**
     * @param ASIDataMapperFactory $dataMapperFactory
     * @param EntityManager $em
     * @param Session $session
     * @param LoggerInterface $logger
     */
    public function __construct(
        ASIDataMapperFactory $dataMapperFactory,
        EntityManager $em,
        Session $session,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->logger = $logger;
        $this->dataMapperFactory = $dataMapperFactory;
    }

    /**
     * @param string $accountingSystem
     * @param Request $request
     * @throws \InvalidArgumentException
     */
    public function processRequestData($accountingSystem, Request $request)
    {
        $dataMapper = $this->dataMapperFactory->getMapper($accountingSystem);

        $integratedModel = $dataMapper->mapData($request);

        $this->session->set(self::SESSION_INTEGRATION_DATA, $integratedModel);

        $this->cachedData = $integratedModel;
    }

    /**
     * @return bool
     */
    public function hasIntegrationData()
    {
        return $this->cachedData instanceof ASIIntegratedModel || $this->session->has(self::SESSION_INTEGRATION_DATA);
    }

    public function removeIntegrationData()
    {
        $this->cachedData = null;
        $this->session->remove(self::SESSION_INTEGRATION_DATA);
    }

    /**
     * @return bool
     */
    public function hasMultiProperties()
    {
        if ($this->get('unitId')) {
            return false;
        }

        $properties = $this->getPropertiesByExternalParameters(
            $this->get('accountingSystem'),
            $this->get('propertyId'),
            $this->get('holdingId')
        );

        if (count($properties) <= 1) {
            return false;
        }
        foreach ($properties as $property) {
            $this->checkPropertyBelongOneGroup($property);
        }

        return true;
    }

    /**
     * @throws \LogicException
     * @return Property|null
     */
    public function getMultiProperties()
    {
        return $this->getPropertiesByExternalParameters(
            $this->get('accountingSystem'),
            $this->get('propertyId'),
            $this->get('holdingId')
        );
    }

    /**
     * @throws \LogicException
     * @return Property|null
     */
    public function getProperty()
    {
        $property = $this->getPropertyByExternalParameters(
            $this->get('accountingSystem'),
            $this->get('propertyId'),
            $this->get('unitId'),
            $this->get('buildingId'),
            $this->get('holdingId')
        );
        if ($property) {
            $this->checkPropertyBelongOneGroup($property);
        }

        return $property;
    }

    /**
     * @return Unit|null
     */
    public function getUnit()
    {
        if ($property = $this->getProperty() and $externalUnitId = $this->get('unitId')) {
            try {
                $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')
                    ->getUnitMappingByPropertyAndExternalUnitId($property, $this->get('propertyId'), $externalUnitId);
                if ($unitMapping) {
                    return $unitMapping->getUnit();
                }
            } catch (NonUniqueResultException $e) {
                throw new \LogicException('Should be exist just one unit mapping');
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getExternalLeaseId()
    {
        return $this->get('leaseId');
    }

    /**
     * @return mixed
     */
    public function getRent()
    {
        return $this->get('rent');
    }

    /**
     * @return array
     */
    public function getAmounts()
    {
        return $this->get('amounts', []);
    }

    /**
     * @return bool
     */
    public function hasPayments()
    {
        return count(array_filter($this->get('amounts', []))) > 0;
    }

    /**
     * Calculate payments that should be payed
     * Return just payment types, that have some preset amount
     *
     * @return array
     */
    public function getPayments()
    {
        return array_keys(
            array_filter($this->getAmounts())
        );
    }

    /**
     * Get list of payment title types that should be payed
     * @return string
     */
    public function getPaymentsList()
    {
        return implode(
            ', ',
            array_map(
                function ($depositAccountType) {
                    return DepositAccountType::title($depositAccountType);
                },
                $this->getPayments()
            )
        );
    }

    /**
     * @param string $paymentType
     */
    public function removePayment($paymentType)
    {
        $amounts = $this->get('amounts', []);
        if (!empty($amounts[$paymentType])) {
            $amounts[$paymentType] = null;
            $this->cachedData['amounts'] = $amounts;
            $this->session->set(self::SESSION_INTEGRATION_DATA, $this->cachedData);
        }
    }

    /**
     * @return null|string
     */
    public function getReturnUrl()
    {
        return $this->get('returnUrl');
    }

    /**
     * @return string
     */
    public function getReturnMethod()
    {
        return $this->get('returnMethod', 'get');
    }


    /**
     * @return array
     */
    public function getReturnParams()
    {
        return $this->get('returnParams', []);
    }

    /**
     * @param array $params
     * @return null|string
     */
    public function getRedirectUrl(array $params = [])
    {
        $redirectUrl = $this->get('returnUrl');
        if ($redirectUrl && !empty($params)) {
            // @parse_url to suppress E_WARNING for invalid urls
            $parsedRedirectUrl = @parse_url($redirectUrl);
            if ($parsedRedirectUrl !== false) {
                $scheme = isset($parsedRedirectUrl['scheme']) ? $parsedRedirectUrl['scheme'] . '://' : '';
                $host = isset($parsedRedirectUrl['host']) ? $parsedRedirectUrl['host'] : '';
                $port = isset($parsedRedirectUrl['port']) ? ':' . $parsedRedirectUrl['port'] : '';
                $path = isset($parsedRedirectUrl['path']) ? $parsedRedirectUrl['path'] : '';
                $query = '?';
                $queryParams = [];
                if (isset($parsedRedirectUrl['query'])) {
                    parse_str($parsedRedirectUrl['query'], $queryParams);
                }
                $query .= http_build_query(array_merge($queryParams, $params));
                $fragment = isset($parsedRedirectUrl['fragment']) ? '#' . $parsedRedirectUrl['fragment'] : '';
                $redirectUrl = $scheme . $host . $port . $path . $query . $fragment;
            }
        }

        return $redirectUrl;
    }

    /**
     * @param Property $property
     * @param $unitName
     * @return ResidentMapping
     */
    public function createResidentMapping(Property $property, $unitName)
    {
        if (!$this->get('residentId')) {
            throw new \InvalidArgumentException('Resident id should be specified.');
        }
        if (!$this->get('propertyId')) {
            throw new \InvalidArgumentException('External property id should be specified.');
        }
        $residentMapping = new ResidentMapping();
        $residentMapping->setResidentId($this->get('residentId'));
        $unit = null;
        if ($unitName !== Unit::SEARCH_UNIT_UNASSIGNED) {
            $unit = $property->searchUnit($unitName);
        }
        try {
            if ($unit) {
                $propertyMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')
                    ->getPropertyMappingByPropertyUnitAndExternalPropertyBelongAccountingSystem(
                        $property,
                        $unit,
                        $this->get('propertyId'),
                        $this->get('accountingSystem')
                    );
            } else {
                $propertyMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')
                    ->getPropertyMappingByPropertyAndExternalPropertyBelongAccountingSystem(
                        $property,
                        $this->get('propertyId'),
                        $this->get('accountingSystem')
                    );
            }
            if (!$propertyMapping) {
                throw new \LogicException('Property mapping should be exist');
            }
        } catch (NonUniqueResultException $e) {
            $this->logger->emergency(
                sprintf(
                    'Found more then one property mapping for parameters:' .
                    ' property #%d,%s accounting system "%s", external property "%s"',
                    $property->getId(),
                    $unitName ? ' and selected unit "' . $unitName. '",' : '',
                    $this->get('accountingSystem'),
                    $this->get('propertyId')
                )
            );
            throw new \LogicException('Should be find just one property mapping with this parameters.');
        }
        $residentMapping->setHolding($propertyMapping->getHolding());

        return $residentMapping;
    }

    /**
     * @param string $paramName
     * @param mixed $defaultValue
     * @return mixed
     */
    protected function get($paramName, $defaultValue = null)
    {
        if (is_null($this->cachedData) && $this->session->has(self::SESSION_INTEGRATION_DATA)) {
            $this->cachedData = $this->session->get(self::SESSION_INTEGRATION_DATA, []);
        }

        if (isset($this->cachedData[$paramName])) {
            return $this->cachedData[$paramName];
        }

        return $defaultValue;
    }

    /**
     * @param string $accountingSystem
     * @param string $externalPropertyId
     * @param string|null $externalUnitId
     * @param string|null $externalBuildingId
     * @param string|null $holdingId
     * @return null|Property
     */
    protected function getPropertyByExternalParameters(
        $accountingSystem,
        $externalPropertyId,
        $externalUnitId = null,
        $externalBuildingId = null,
        $holdingId = null
    ) {
        try {
            if ($externalUnitId) {
                return $this->em->getRepository('RjDataBundle:Property')
                    ->getPropertyByExternalPropertyUnitIds(
                        $accountingSystem,
                        $externalPropertyId,
                        $externalUnitId,
                        $externalBuildingId,
                        $holdingId
                    );
            } else {
                return $this->em->getRepository('RjDataBundle:Property')
                    ->getPropertyByExternalPropertyId(
                        $accountingSystem,
                        $externalPropertyId,
                        $holdingId
                    );
            }
        } catch (NonUniqueResultException $e) {
            $this->logger->emergency(
                sprintf(
                    'Found more then one property for parameters: accounting system "%s", external property "%s"%s',
                    $accountingSystem,
                    $externalPropertyId,
                    $externalUnitId  ? ', external unit "' . $externalUnitId . '"' : ''
                )
            );
            throw new \LogicException('Should be found just 1 property by external parameters');
        }
    }

    /**
     * @param string $accountingSystem
     * @param string $externalPropertyId
     * @param string|null $holdingId
     * @return \RentJeeves\DataBundle\Entity\Property[]
     */
    protected function getPropertiesByExternalParameters($accountingSystem, $externalPropertyId, $holdingId = null)
    {
        return $this->em->getRepository('RjDataBundle:Property')
            ->getPropertiesByExternalPropertyId($accountingSystem, $externalPropertyId, $holdingId);
    }

    /**
     * @param Property $property
     * @throws \LogicException
     */
    protected function checkPropertyBelongOneGroup(Property $property)
    {
        try {
            $this->em->getRepository('RjDataBundle:Property')->checkPropertyBelongOneGroup($property);
        } catch (NonUniqueResultException $e) {
            $this->logger->emergency(
                sprintf(
                    'Property #%d should belong just to one group.',
                    $property->getId()
                )
            );
            throw new \LogicException('Property should belong just to one group.');
        } catch (NoResultException $e) {
            $this->logger->emergency(
                sprintf(
                    'Property #%d should have units that belong just to one group.',
                    $property->getId()
                )
            );
            throw new \LogicException('Property should have units that belong just to one group.');
        }
    }
}
