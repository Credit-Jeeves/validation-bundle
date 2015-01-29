<?php

namespace RentJeeves\LandlordBundle\Services;

use CreditJeeves\DataBundle\Entity\Holding;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Doctrine\ORM\EntityManager;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyMapping;

/**
 * @Service("property_mapping.manager")
 */
class PropertyMappingManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Holding
     */
    protected $holding;

    /**
     * @InjectParams({
     *     "em"              = @Inject("doctrine.orm.entity_manager"),
     *     "securityContext" = @Inject("security.context")
     * })
     */
    public function __construct(EntityManager $em, $securityContext)
    {
        $this->em  = $em;
        $this->holding = $securityContext->getToken()->getUser()->getHolding();
    }

    /**
     * @param integer $propertyId
     * @param string $externalPropertyId
     *
     * @return PropertyMapping
     */
    public function createPropertyMapping($propertyId, $externalPropertyId)
    {
        /**
         * @var $propertyMapping PropertyMapping
         */
        $propertyMapping = $this->em->getRepository('RjDataBundle:PropertyMapping')->findOneBy(
            array(
                'property'              => $propertyId,
                'holding'               => $this->holding->getId(),
            )
        );

        if (empty($propertyMapping)) {
            /** @var $property Property */
            $property = $this->em->getRepository('RjDataBundle:Property')->find($propertyId);
            $propertyMapping = new PropertyMapping();
            $propertyMapping->setHolding($this->holding);
            $propertyMapping->setProperty($property);
            $propertyMapping->setExternalPropertyId($externalPropertyId);
            $this->em->persist($propertyMapping);
            $this->em->flush();
        }

        return $propertyMapping->getProperty()->getPropertyMappingByHolding($this->holding);
    }
}
