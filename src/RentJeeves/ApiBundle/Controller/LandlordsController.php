<?php

namespace RentJeeves\ApiBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use RentJeeves\ApiBundle\ResponseEntity\Landlord as LandlordResponse;
use RentJeeves\ApiBundle\Services\ProcessAssignment;
use RentJeeves\DataBundle\Entity\Landlord;
use RentJeeves\DataBundle\Entity\LandlordRepository;
use RentJeeves\DataBundle\Entity\Property;
use RentJeeves\DataBundle\Entity\Unit;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Forms\InviteLandlordType;
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
     * @param ParamFetcher $paramFetcher
     *
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
     *   name="second_name",
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
    public function newLandlordAction(ParamFetcher $paramFetcher)
    {
        /** @var InviteLandlord $inviteProcesser */
        $inviteProcesser = $this->get('invite.landlord');

        $form = $this->createForm(
            new InviteLandlordType()
        );

        $request = $this->get('request');

        $paramFetcher->all();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $invite = $form->getData();
            $invite->setTenant($this->getUser());

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
     * @ApiDoc(
     *     resource=true,
     *     section="Landlords",
     *     description="Assign unit to landlord and create contract.",
     *     statusCodes={
     *         200="Success",
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
        /** @var ProcessAssignment $assignmentProcesser */
        $assignmentProcesser = $this->get('landlord.process_assignment');

        $response = $assignmentProcesser->assignment($landlordId, $unitId);

        $statusCode = isset($response['status_code']) ? $response['status_code'] : Codes::HTTP_OK;

        return $this->view($response, $statusCode);
    }

    /**
     * @param $landlordId
     * @param $propertyId
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Landlords",
     *     description="Assign property to landlord and create contract.",
     *     statusCodes={
     *         200="Success",
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
        /** @var ProcessAssignment $assignmentProcesser */
        $assignmentProcesser = $this->get('landlord.process_assignment');

        $response = $assignmentProcesser->assignment($landlordId, $propertyId, ProcessAssignment::ASSIGNMENT_PROPERTY);

        $statusCode = isset($response['status_code']) ? $response['status_code'] : Codes::HTTP_OK;

        return $this->view($response, $statusCode);
    }
}

