<?php

namespace RentJeeves\ApiBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\ApiBundle\ResponseEntity\Property as PropertyResponse;
use RentJeeves\ApiBundle\ResponseEntity\Unit as UnitResponse;
use RentJeeves\DataBundle\Entity\PropertyRepository;
use RentJeeves\DataBundle\Entity\Unit;
use RentJeeves\DataBundle\Entity\UnitRepository;
use RentJeeves\PublicBundle\Form\PropertyType;
use RentJeeves\PublicBundle\Form\UnitType;
use stdClass;
use RentJeeves\CoreBundle\Services\PropertyProcess;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     *         500= {
     *          "Something wrong in request"
     *         }
     *     }
     * )
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
     *     description="Get detailed information about a RentTrack property using full address seach.",
     *     statusCodes={
     *         200="Success",
     *         204="No content with supplied parameters",
     *         400="Error validating data, such as address not found by Google Maps",
     *         500= {
     *          "Something wrong in request"
     *         }
     *     }
     * )
     * @Rest\View(serializerGroups={"PropertyDetails"})
     * @Rest\QueryParam(
     *   name="address",
     *   strict=true,
     *   nullable=false,
     *   description="Full normalized address as displayed by Google Maps"
     * )
     *
     * @return array
     */
    public function searchPropertyAction(ParamFetcher $paramFetcher)
    {
        $address = $paramFetcher->get('address');

        /** @var PropertyProcess $propertyProcesser */
        $propertyProcesser = $this->get('property.process');

        $property = $propertyProcesser->getPropertyByAddress($address);

        if (false === $property) {
            return $this->view([
                'status' => 'Error',
                'status_code' => Codes::HTTP_BAD_REQUEST,
                'message' => 'Address is invalid'
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
     *         500= {
     *          "Something went wrong with the request"
     *         }
     *     }
     * )
     * @Rest\View(serializerGroups={"UnitDetails"})
     * @Rest\Get("/properties/{propertyId}/units/{unitId}")
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
     *         500= {
     *          "Something went wrong with the request"
     *         }
     *     }
     * )
     * @Rest\View(serializerGroups={"UnitShort"})
     * @Rest\Get("/properties/{propertyId}/units")
     *
     * @return array
     */
    public function cgetPropertyUnitsAction($propertyId)
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
     *         200="Success",
     *         400="Error validating data",
     *         500= {
     *          "Something went wrong with the request"
     *         }
     *     }
     * )
     * @Rest\View(serializerGroups={"PropertyDetails", "UnitDetails"})
     * @Rest\Post()
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
     *
     * @return array
     */
    public function createPropertiesAction(ParamFetcher $paramFetcher)
    {

        return $this->process($paramFetcher, $this->get('property.process'), $this->getDoctrine()->getManager());
    }

    protected function process(ParamFetcher $paramFetcher, PropertyProcess $propertyProcesser, EntityManager $em)
    {
        if ($address = $paramFetcher->get('address')) {
            $property = $propertyProcesser->getPropertyByAddress($address);
            $single = $paramFetcher->get('is_single');

            if (false === $property) {
                return $this->view([
                    'status' => 'Error',
                    'status_code' => Codes::HTTP_BAD_REQUEST,
                    'message' => 'Address is invalid'
                ], Codes::HTTP_BAD_REQUEST);
            } elseif (!$this->isNew($property) && $property->getIsSingle() != $single) {
                $s = !$single ? '' : ' not';
                return $this->view([
                    'status' => 'Error',
                    'status_code' => Codes::HTTP_BAD_REQUEST,
                    'message' => sprintf('Property is%s standalone', $s)
                ], Codes::HTTP_BAD_REQUEST);
            }

            if (!$single) {
                if (!($unitName = $paramFetcher->get('unit_name'))) {
                    return $this->view([
                        'status' => 'Error',
                        'status_code' => Codes::HTTP_BAD_REQUEST,
                        'message' => 'Unit is required for not standalone property.'
                    ], Codes::HTTP_BAD_REQUEST);
                }

                $unit = (new Unit())->setName($unitName);
                $property->addUnit($unit);
                $unit->setProperty($property);
            }

            $em->persist($property);
            $em->flush($property);

            $response = ['status' => 'OK', 'property_id' => $property->getId()];

            if (isset($unit)) {
                $response['unit_id'] = $unit->getId();
            }

            return $response;
        }

        return $this->view([
            'status' => 'Error',
            'status_code' => Codes::HTTP_BAD_REQUEST,
            'message' => 'Address is required'
        ], Codes::HTTP_BAD_REQUEST);
    }

    protected function isNew($entity)
    {
        return !$entity->getId();
    }
}
