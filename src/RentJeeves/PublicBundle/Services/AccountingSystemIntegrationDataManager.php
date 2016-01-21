<?php

namespace RentJeeves\PublicBundle\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\ResidentMapping;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Enum\AccountingSystem;
use RentJeeves\DataBundle\Enum\DepositAccountType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * DI\Service('accounting_system.integration.data_manager')
 */
class AccountingSystemIntegrationDataManager
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
     * @var array
     */
    protected $cachedData;

    /**
     * @param EntityManager $em
     * @param Session $session
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManager $em, Session $session, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->session = $session;
        $this->logger = $logger;
    }

    /**
     * @param string $accountingSystem
     * @param Request $request
     * @throws \InvalidArgumentException
     */
    public function processRequestData($accountingSystem, Request $request)
    {
        $accountingSystem = array_search($accountingSystem, AccountingSystem::$importMapping);
        if ($accountingSystem === false) {
            throw new \InvalidArgumentException('Accounting system type is invalid.');
        }
        $processedData = ['accsys' => $accountingSystem];

        $requiredParams = ['resid', 'leaseid', 'propid'];
        foreach ($requiredParams as $paramName) {
            if (!$paramValue = $request->get($paramName)) {
                throw new \InvalidArgumentException(
                    sprintf('Please provide required parameter "%s".', $paramName)
                );
            }
            $processedData[$paramName] = $paramValue;
        }

        $optionalParams = ['unitid', 'rent', 'redirect'];
        foreach ($optionalParams as $paramName) {
            if ($paramValue = $request->get($paramName)) {
                $processedData[$paramName] = $paramValue;
            }
        }

        $amounts = [];
        if ($appFee = $request->get('appfee')) {
            $amounts[DepositAccountType::APPLICATION_FEE] = $appFee;
        }
        if ($secDep = $request->get('secdep')) {
            $amounts[DepositAccountType::SECURITY_DEPOSIT] = $secDep;
        }
        $processedData['amounts'] = $amounts;

        $this->session->set(self::SESSION_INTEGRATION_DATA, $processedData);

        $this->cachedData = $processedData;
    }

    /**
     * @return bool
     */
    public function hasIntegrationData()
    {
        return !empty($this->cachedData) || $this->session->has(self::SESSION_INTEGRATION_DATA);
    }

    public function removeIntegrationData()
    {
        $this->cachedData = null;
        $this->session->remove(self::SESSION_INTEGRATION_DATA);
    }

    /**
     * @throws \LogicException
     * @return Property|null
     */
    public function getProperty()
    {
        $property = $this->getPropertyByExternalParameters(
            $this->get('accsys'),
            $this->get('propid'),
            $this->get('unitid')
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
        if ($property = $this->getProperty() and $externalUnitId = $this->get('unitid')) {
            try {
                $unitMapping = $this->em->getRepository('RjDataBundle:UnitMapping')
                    ->getUnitMappingByPropertyAndExternalUnitId($property, $this->get('propid'), $externalUnitId);
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
        return $this->get('leaseid');
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
     * @param array $params
     * @return null|string
     */
    public function getRedirectUrl(array $params = [])
    {
        $redirectUrl = $this->get('redirect');
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
        if (!$this->get('resid')) {
            throw new \InvalidArgumentException('Resident id should be specified.');
        }
        if (!$this->get('propid')) {
            throw new \InvalidArgumentException('External property id should be specified.');
        }
        if (!$this->get('accsys')) {
            throw new \InvalidArgumentException('Accounting system should be specified.');
        }
        $residentMapping = new ResidentMapping();
        $residentMapping->setResidentId($this->get('resid'));
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
                        $this->get('propid'),
                        $this->get('accsys')
                    );
            } else {
                $propertyMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')
                    ->getPropertyMappingByPropertyAndExternalPropertyBelongAccountingSystem(
                        $property,
                        $this->get('propid'),
                        $this->get('accsys')
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
                    $this->get('accsys'),
                    $this->get('propid')
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
     * @return null|Property
     */
    protected function getPropertyByExternalParameters(
        $accountingSystem,
        $externalPropertyId,
        $externalUnitId = null
    ) {
        try {
            if ($externalUnitId) {
                return $this->em->getRepository('RjDataBundle:Property')
                    ->getPropertyByExternalPropertyUnitIds($accountingSystem, $externalPropertyId, $externalUnitId);
            } else {
                return $this->em->getRepository('RjDataBundle:Property')
                    ->getPropertyByExternalPropertyId($accountingSystem, $externalPropertyId);
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
