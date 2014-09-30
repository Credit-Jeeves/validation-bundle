<?php

namespace RentJeeves\ApiBundle\Services;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Util\Codes;
use Gedmo\DoctrineExtensions;
use JMS\DiExtraBundle\Annotation as DI;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;

/**
 * Class ProcessAssignment
 * @package RentJeeves\ApiBundle\Services
 *
 * @DI\Service("landlord.process_assignment")
 */
class ProcessAssignment
{
    const ASSIGNMENT_UNIT = 'Unit';

    const ASSIGNMENT_PROPERTY = 'Property';
    /**
     * @var EntityManager
     * @DI\Inject("doctrine.orm.default_entity_manager", required = true)
     */
    public $em;

    public function assignment($landlordId, $id, $type = self::ASSIGNMENT_UNIT)
    {
        $repo = $this->em->getRepository('RjDataBundle:Landlord');

        /** @var Landlord $landlord */
        $landlord = $repo->find($landlordId);

        if (!$landlord) {
            return [
                'status' => 'error',
                'status_code' => Codes::HTTP_NOT_FOUND,
                'message' => 'Landlord is not found.'
            ];
        }
        $repo = $this->em->getRepository('RjDataBundle:' . $type);
        $entity = $repo->find($id);

        if (!$entity) {
            return [
                'status' => 'error',
                'status_code' => Codes::HTTP_NOT_FOUND,
                'message' => printf('%s is not found.', $type)
            ];
        }

        switch ($type) {
            case self::ASSIGNMENT_UNIT:
                /** @var Unit $unit */
                $unit = $entity;
                $property = $unit->getProperty();
                break;
            case self::ASSIGNMENT_PROPERTY:
                /** @var Property $property */
                $property = $entity;
                $unit = null;
                break;
            default:
                return [
                    'status' => 'error',
                    'status_code' => Codes::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Entity is not found.'
                ];
        }

        $errors = $this->validate($landlord, $property, $unit);

        if (count($errors) > 0) {
            return [
                'status' => 'error',
                'status_code' => Codes::HTTP_BAD_REQUEST,
                'message' => 'Validation errors',
                'errors' => $errors
            ];
        }

        if ($unit) {
            $unit->setHolding($landlord->getHolding());
            $unit->setGroup($landlord->getCurrentGroup());
            $this->em->persist($unit);
            $this->em->flush($unit);
        }

        $conn = $this->em->getConnection();
        $groupProperty = $conn
            ->fetchAssoc(
                'SELECT * FROM rj_group_property WHERE property_id= :property AND group_id = :group',
                [
                    'property' => $property->getId(),
                    'group' => $landlord->getCurrentGroup()->getId()
                ]
            );

        if (!$groupProperty) {
            $conn->insert('rj_group_property', [
                'property_id' => $property->getId(),
                'group_id' => $landlord->getCurrentGroup()->getId()
            ]);
        }

        return [
            'status' => 'OK',
        ];
    }

    protected function validate(Landlord $landlord, Property $property, Unit $unit = null)
    {
        $errors = [];
        if ($landlord->getGroups()->count() > 1) {
            $errors['landlord'] = 'Multi groups is not supported.';
        }

        if (!$property->getIsSingle() && is_null($unit)) {
            $errors['property'] = 'Property must be standalone. Not supports multiple assignment.';
        }

        if ($unit && $unit->getHolding() && $unit->getGroup() && ($unit->getGroup() != $landlord->getCurrentGroup())) {
            $errors['unit'] = 'Unit cannot be reassigned.';
        }

        return $errors;
    }
}
