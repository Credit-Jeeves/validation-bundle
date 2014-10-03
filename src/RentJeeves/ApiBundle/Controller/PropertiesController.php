<?php

namespace RentJeeves\ApiBundle\Controller;

use CreditJeeves\CoreBundle\Translation\Translator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Util\Codes;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\ApiBundle\ResponseEntity\Property as PropertyResponse;
use RentJeeves\ApiBundle\ResponseEntity\Unit as UnitResponse;
use RentJeeves\DataBundle\Entity\PropertyRepository;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitRepository;
use RentJeeves\CoreBundle\Services\PropertyProcess;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class PropertiesController extends Controller
{
    /**
     * @param $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Properties",
     *     description="This call allows the application to get details information about property by id.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         400="Error validate data",
     *         500="Something wrong in request"
     *     }
     * )
     * @Rest\Get("/properties/{id}")
     * @Rest\View(serializerGroups={"PropertyDetails"})
     *
     * @return array
     */
    public function getPropertyAction($id)
    {
        /** @var PropertyRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Property');
        /** @var Property $property */
        $property = $repo->find($id);
        if ($property) {
            return ['status' => 'OK', 'property' => new PropertyResponse($property)];
        }

        return $this->view([], Codes::HTTP_NO_CONTENT);
    }

    /**
     * @param ParamFetcher $paramFetcher
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Properties",
     *     description="Get detailed information about a RentTrack property using full address search.",
     *     statusCodes={
     *         200="Success",
     *         204="No content with supplied parameters",
     *         400="Error validating data, such as address not found by Google Maps",
     *         500="Something wrong in request"
     *     }
     * )
     * @Rest\Get("/properties")
     * @Rest\QueryParam(
     *   name="address",
     *   strict=true,
     *   nullable=false,
     *   description="Full normalized address as displayed by Google Maps"
     * )
     * @Rest\View(serializerGroups={"PropertyDetails", "RentJeevesApi"})
     *
     * @return array
     */
    public function searchPropertyAction(ParamFetcher $paramFetcher)
    {
        $address = $paramFetcher->get('address');

        /** @var Translator $translator */
        $translator = $this->get('translator');
        /** @var PropertyProcess $propertyProcesser */
        $propertyProcesser = $this->get('property.process');

        $property = $propertyProcesser->getPropertyByAddress($address);

        if (is_null($property)) {
            return $this->view([
                'status' => 'Error',
                'status_code' => Codes::HTTP_BAD_REQUEST,
                'message' => $translator->trans('api.error.property.address_invalid')
            ], Codes::HTTP_BAD_REQUEST); // TODO Error Handler Check
        } elseif ($this->isNew($property)) {
            return $this->view([], Codes::HTTP_NO_CONTENT);
        } else {
            return ['status' => 'OK', 'property' => new PropertyResponse($property)];
        }
    }

    /**
     * @param $propertyId
     * @param $unitId
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Properties",
     *     description="Get detailed information about a RentTrack unit by its id
                        and property id.",
     *     statusCodes={
     *         200="Success",
     *         204="No content with supplied parameters",
     *         400="Error validating data",
     *         500="Something went wrong with the request"
     *     }
     * )
     * @Rest\Get("/properties/{propertyId}/units/{unitId}")
     * @Rest\View(serializerGroups={"UnitDetails"})
     *
     * @return array
     */
    public function getPropertyUnitAction($propertyId, $unitId)
    {
        /** @var UnitRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Unit');
        /** @var Unit $unit */
        $unit = $repo->findOneBy(['id' => $unitId, 'property' => $propertyId]);
        if ($unit) {
            return ['status' => 'OK', 'unit' => new UnitResponse($unit)];
        }

        return $this->view([], Codes::HTTP_NO_CONTENT);
    }

    /**
     * @param $propertyId
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Properties",
     *     description="Get all the units that belong to a property.",
     *     statusCodes={
     *         200="Success",
     *         204="No content with supplied parameters",
     *         400="Error validating data",
     *         500="Something went wrong with the request"
     *     }
     * )
     * @Rest\Get("/properties/{propertyId}/units")
     * @Rest\View(serializerGroups={"UnitShort"})
     *
     * @return array
     */
    public function getPropertyUnitsAction($propertyId)
    {
        /** @var PropertyRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Property');
        /** @var Property $property */
        $property = $repo->find($propertyId);

        if ($property) {
            return ['status' => 'OK', 'units' => (new PropertyResponse($property))->getUnits()];
        }

        return $this->view([], Codes::HTTP_NO_CONTENT);
    }

    /**
     * @param ParamFetcher $paramFetcher
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Properties",
     *     description="Create a unit, or a property with a unit.",
     *     statusCodes={
     *         201="Success created",
     *         400="Error validating data",
     *         500="Something went wrong with the request"
     *     }
     * )
     * @Rest\Post("/properties")
     * @Rest\RequestParam(
     *   name="address",
     *   strict=true,
     *   nullable=false
     * )
     * @Rest\RequestParam(
     *   name="is_single",
     *   strict=true,
     *   nullable=false
     * )
     * @Rest\RequestParam(
     *   name="unit_name",
     *   strict=true,
     *   nullable=true
     * )
     * @Rest\View(serializerGroups={"PropertyDetails", "UnitDetails"})
     *
     * @return array
     */
    public function createPropertiesAction(ParamFetcher $paramFetcher)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var PropertyProcess $propertyProcesser */
        $propertyProcesser = $this->get('property.process');
        /** @var Translator $translator */
        $translator = $this->get('translator');

        if ($address = $paramFetcher->get('address')) {
            $property = $propertyProcesser->getPropertyByAddress($address);
            $isSingle = $paramFetcher->get('is_single');

            if (false === $property) {
                return $this->view([
                    'status' => 'Error',
                    'status_code' => Codes::HTTP_BAD_REQUEST,
                    'message' => $translator->trans('api.error.property.address_invalid')
                ], Codes::HTTP_BAD_REQUEST);
            } elseif (!$this->isNew($property) && $property->isSingle() != $isSingle) {
                return $this->view([
                    'status' => 'Error',
                    'status_code' => Codes::HTTP_BAD_REQUEST,
                    'message' => $translator->trans('api.error.change_property', ['%property%' => 'IS_SINGLE'])
                ], Codes::HTTP_BAD_REQUEST);
            }

            if (!$isSingle) {
                if (!($unitName = $paramFetcher->get('unit_name'))) {
                    return $this->view([
                        'status' => 'Error',
                        'status_code' => Codes::HTTP_BAD_REQUEST,
                        'message' => $translator->trans('api.error.property.required_unit')
                    ], Codes::HTTP_BAD_REQUEST);
                }

                $unit = (new Unit())->setName($unitName);
                $property->addUnit($unit);
                $unit->setProperty($property);
            }

            $property->setIsSingle($isSingle);
            $em->persist($property);
            $em->flush($property);

            $response = ['status' => 'OK', 'property_id' => $property->getId()];

            if (isset($unit)) {
                $response['unit_id'] = $unit->getId();
            }

            return $this->view($response, Codes::HTTP_CREATED);
        }

        return $this->view([
            'status' => 'Error',
            'status_code' => Codes::HTTP_BAD_REQUEST,
            'message' => $translator->trans('api.error.required_property', ['%property%' => 'Address'])
        ], Codes::HTTP_BAD_REQUEST);
    }

    protected function isNew($entity)
    {
        return !$entity->getId();
    }
}
