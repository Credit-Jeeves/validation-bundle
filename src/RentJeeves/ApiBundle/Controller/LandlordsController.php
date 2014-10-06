<?php

namespace RentJeeves\ApiBundle\Controller;

use CreditJeeves\CoreBundle\Translation\Translator;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use RentJeeves\ApiBundle\ResponseEntity\Landlord as LandlordResponse;
use RentJeeves\ApiBundle\Services\LandlordAssignment;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\LandlordRepository;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\PropertyRepository;
use RentJeeves\DataBundle\Entity\Unit;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Forms\InviteLandlordType;
use RentJeeves\DataBundle\Entity\UnitRepository;
use RentJeeves\TenantBundle\Services\InviteLandlord;
use Symfony\Component\HttpFoundation\Request;

class LandlordsController extends Controller
{
    /**
     * @param $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Landlords",
     *     description="This call allows the application to get details information about landlord by id.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         400="Error validate data",
     *         500="Something wrong in request"
     *     }
     * )
     * @Rest\Get("/landlords/{id}")
     * @Rest\View(serializerGroups={"LandlordDetails"})
     *
     * @return array
     */
    public function getLandlordAction($id)
    {
        /** @var LandlordRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Landlord');
        /** @var Landlord $property */
        $landlord = $repo->find($id);
        if ($landlord) {
            return ['status' => 'OK', 'landlord' => new LandlordResponse($landlord)];
        }

        return $this->view([], Codes::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     section="Landlords",
     *     description="Create invite for landlord.",
     *     statusCodes={
     *         201="Success",
     *         400="Error validating data",
     *         500="Something went wrong with the request"
     *     }
     * )
     * @Rest\Post("/landlords")
     * @Rest\RequestParam(
     *   name="unit_id",
     *   strict=true,
     *   nullable=true
     * )
     * @Rest\RequestParam(
     *   name="first_name",
     *   strict=true,
     *   nullable=true
     * )
     * @Rest\RequestParam(
     *   name="last_name",
     *   strict=true,
     *   nullable=true
     * )
     * @Rest\RequestParam(
     *   name="phone",
     *   strict=true,
     *   nullable=true
     * )
     * @Rest\RequestParam(
     *   name="email",
     *   strict=true,
     *   nullable=false
     * )
     * @Rest\View(serializerGroups={"LandlordDetails", "RentJeevesApi"})
     *
     * @return array
     */
    public function newLandlordAction()
    {
        /** @var InviteLandlord $inviteProcesser */
        $inviteProcesser = $this->get('invite.landlord');

        $form = $this->createForm(
            new InviteLandlordType()
        );

        $request = $this->get('request');

        $form->handleRequest($request);

        if ($form->isValid()) {
            $invite = $form->getData();
            $landlord = $inviteProcesser->invite($invite, $this->getUser());

            return $this->view([
                'status' => 'OK',
                'landlord_id' => $landlord->getId()
            ], Codes::HTTP_CREATED);
        }

        return $form;
    }

    /**
     * @param $landlordId
     * @param $unitId
     *
     * @todo Need change this, or use POST with cmd assignment or use header
     *  "Link: <{host}/api/units/{id}>; rel="unit"
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Landlords",
     *     description="Assign unit to landlord and create contract.",
     *     statusCodes={
     *         204="Accepted",
     *         400="Error validating data",
     *         404="Landlord is not found",
     *         500="Something went wrong with the request"
     *     }
     * )
     * @Rest\Link("/landlords/{landlordId}/units/{unitId}")
     * @Rest\View(serializerGroups={"LandlordDetails", "RentJeevesApi"})
     *
     * @return array
     */
    public function assignUnitToLandlordAction($landlordId, $unitId)
    {
        /** @var Translator $translator */
        $translator = $this->get('translator');
        /** @var LandlordRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Landlord');
        /** @var Landlord $landlord */
        $landlord = $repo->find($landlordId);

        if (!$landlord) {
            return $this->view([
                'status' => 'error',
                'status_code' => Codes::HTTP_NOT_FOUND,
                'message' => $translator->trans('api.error.property_not_found', ['%property%' => 'Landlord'])
            ], Codes::HTTP_NOT_FOUND);
        }

        /** @var UnitRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Unit');
        /** @var Unit $unit */
        $unit = $repo->find($unitId);

        if (!$unit) {
            return $this->view([
                'status' => 'error',
                'status_code' => Codes::HTTP_NOT_FOUND,
                'message' => $translator->trans('api.error.property_not_found', ['%property%' => 'Unit'])
            ], Codes::HTTP_NOT_FOUND);
        }

        /** @var LandlordAssignment $assignmentProcesser */
        $assignmentProcesser = $this->get('landlord.assignment');

        if ($assignmentProcesser->assignmentUnit($landlord, $unit)) {
            return $this->view([
                'status' => 'OK'
            ], Codes::HTTP_ACCEPTED);
        }

        return $this->view([
            'status' => 'Error',
            'status_code' => Codes::HTTP_BAD_REQUEST,
            'message' => $translator->trans('api.validation_error'),
            'errors' => $assignmentProcesser->getErrors()
        ], Codes::HTTP_BAD_REQUEST);
    }

    /**
     * @param $landlordId
     * @param $propertyId
     *
     * @todo Need change this, or use POST with cmd assignment or use header
     *  "Link: <{host}/api/properties/{id}>; rel="property"
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Landlords",
     *     description="Assign property to landlord and create contract.",
     *     statusCodes={
     *         204="Accepted",
     *         400="Error validating data",
     *         404="Landlord is not found",
     *         500="Something went wrong with the request"
     *     }
     * )
     * @Rest\Link("/landlords/{landlordId}/properties/{propertyId}")
     * @Rest\View(serializerGroups={"LandlordDetails", "RentJeevesApi"})
     *
     * @return array
     */
    public function assignPropertyToLandlordAction($landlordId, $propertyId)
    {
        /** @var Translator $translator */
        $translator = $this->get('translator');
        /** @var LandlordRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Landlord');
        /** @var Landlord $landlord */
        $landlord = $repo->find($landlordId);

        if (!$landlord) {
            return $this->view([
                'status' => 'Error',
                'status_code' => Codes::HTTP_NOT_FOUND,
                'message' => $translator->trans('api.error.property_not_found', ['%property%' => 'Landlord'])
            ], Codes::HTTP_NOT_FOUND);
        }

        /** @var PropertyRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Unit');
        /** @var Property $property */
        $property = $repo->find($propertyId);

        if (!$property) {
            return $this->view([
                'status' => 'Error',
                'status_code' => Codes::HTTP_NOT_FOUND,
                'message' => $translator->trans('api.error.property_not_found', ['%property%' => 'Property'])
            ], Codes::HTTP_NOT_FOUND);
        }

        /** @var LandlordAssignment $assignmentProcesser */
        $assignmentProcesser = $this->get('landlord.assignment');

        if ($assignmentProcesser->assignmentProperty($landlord, $property)) {
            return $this->view([
                'status' => 'OK'
            ], Codes::HTTP_ACCEPTED);
        }

        return $this->view([
            'status' => 'Error',
            'status_code' => Codes::HTTP_BAD_REQUEST,
            'message' => $translator->trans('api.validation_error'),
            'errors' => $assignmentProcesser->getErrors()
        ], Codes::HTTP_BAD_REQUEST);
    }
}
