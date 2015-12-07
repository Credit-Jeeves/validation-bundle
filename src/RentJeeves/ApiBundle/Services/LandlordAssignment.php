<?php

namespace RentJeeves\ApiBundle\Services;

use CreditJeeves\CoreBundle\Translation\Translator;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;

/**
 * @DI\Service("landlord.assignment")
 */
class LandlordAssignment
{
    protected $errors = [];
    /**
     * @var EntityManager
     * @DI\Inject("doctrine.orm.default_entity_manager", required = true)
     */
    public $em;

    /**
     * @var Translator
     * @DI\Inject("translator")
     */
    public $translator;

    /**
     * @param Landlord $landlord
     * @param Property $property
     * @return bool
     */
    public function assignmentProperty(Landlord $landlord, Property $property)
    {
        if ($this->validate($landlord, $property)) {
            if (!$property->hasGroup($landlord->getCurrentGroup())) {
                $property->addPropertyGroup($landlord->getCurrentGroup());
            }
            $this->em->persist($property);
            $this->em->flush($property);

            return true;
        }

        return false;
    }

    /**
     * @param Landlord $landlord
     * @param Unit $unit
     * @return bool
     */
    public function assignmentUnit(Landlord $landlord, Unit $unit)
    {
        $property = $unit->getProperty();

        if ($this->validate($landlord, $property, $unit)) {
            $unit->setHolding($landlord->getHolding());
            $unit->setGroup($landlord->getCurrentGroup());
            $this->em->persist($unit);

            if (!$property->hasGroup($landlord->getCurrentGroup())) {
                $property->addPropertyGroup($landlord->getCurrentGroup());
            }
            $this->em->persist($property);
            $this->em->flush($property);
            $this->em->flush($unit);

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param Landlord $landlord
     * @param Property $property
     * @param Unit $unit
     * @return bool
     */
    protected function validate(Landlord $landlord, Property $property, Unit $unit = null)
    {
        if ($landlord->getGroups()->count() > 1) {
            $this->errors['landlord'] = $this->translator->trans('api.error.landlord.assignment.multi_groups');
        }

        $propertyAddress = $property->getPropertyAddress();
        if (!$propertyAddress->isSingle() && is_null($unit)) {
            $errors['property'] = $this->translator->trans('api.error.landlord.assignment.multi_assignment');
        }

        if ($unit && $unit->getHolding() && $unit->getGroup() && ($unit->getGroup() != $landlord->getCurrentGroup())) {
            $errors['unit'] = $this->translator->trans('api.error.landlord.assignment.reassignment');
        }

        return (count($this->errors) ==0);
    }
}
