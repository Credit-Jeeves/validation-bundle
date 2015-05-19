<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use CreditJeeves\DataBundle\Entity\Pidkiq;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;
use RentJeeves\ApiBundle\Response\Pidkiq as ResponseEntity;
use RentJeeves\ComponentBundle\PidKiqProcessor\PidKiqProcessorInterface;
use RentJeeves\ComponentBundle\PidKiqProcessor\PidKiqStateAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdentityVerificationController extends Controller
{
    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Identity Verification",
     *     description="Create resource for start tenant identity verification.",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Post("/identity_verification")
     * @Rest\View(serializerGroups={"Base", "IdentityVerificationDetails", "ApiErrors"}, statusCode=201)
     * @RequestParam(
     *     name="client_ip_address",
     *     description=" The required field should be set using your IP address of your end-user's client."
     * )
     */
    public function startIdentityVerificationAction(Request $request)
    {
        /** @var PidKiqProcessorInterface|PidKiqStateAwareInterface $pidKiqProcessor */
        $pidKiqProcessor = $this->get('experian.pidkiq_processor.factory')->getPidKiqProcessor();

        $pidKiqProcessor->retrieveQuestions();

        return $this->get('response_resource.factory')
            ->getResponse($pidKiqProcessor->getPidkiqModel())
            ->setMessage($pidKiqProcessor->getMessage());
    }

    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Identity Verification",
     *     description="Get details for a specific resource.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Resource has already expired or doesn\'t exist.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/identity_verification/{id}")
     * @Rest\View(serializerGroups={"Base", "IdentityVerificationDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity

     */
    public function getIdentityVerificationAction($id)
    {
        /** @var Pidkiq $pidkiq */
        $pidkiq = $this->getDoctrine()
            ->getRepository('DataBundle:Pidkiq')
            ->findNotExpiredByUserAndId($id, $this->getUser());

        if ($pidkiq) {
            return $this->get('response_resource.factory')->getResponse($pidkiq);
        }

        throw new NotFoundHttpException('Resource has already expired or doesn\'t exist.');
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Identity Verification",
     *     description="Verification process.",
     *     statusCodes={
     *         204="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         404="Resource has already expired or doesn\'t exist.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Put("/identity_verification/{id}")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=204)
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     * This is needed for correct parsing url and get id
     * @RequestParam(
     *     name="answers",
     *     strict=true,
     *     array=true,
     *     description="Array with answers."
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function sendIdentityVerificationAction($id, Request $request)
    {
        /** @var Pidkiq $pidkiq */
        $pidkiq = $this->getDoctrine()
            ->getRepository('DataBundle:Pidkiq')
            ->findNotExpiredByUserAndId($id, $this->getUser(), '60 minutes');

        if (!$pidkiq) {
            throw new NotFoundHttpException('Resource has already expired or doesn\'t exist.');
        }

        /** @var PidKiqProcessorInterface|PidKiqStateAwareInterface $pidKiqProcessor */
        $pidKiqProcessor = $this->get('experian.pidkiq_processor.factory')->getPidKiqProcessor();

        if (!$pidKiqProcessor->processAnswers($request->get('answers'))) {
            throw new BadRequestHttpException($pidKiqProcessor->getMessage());
        }

        return $this->get('response_resource.factory')
            ->getResponse($pidKiqProcessor->getPidkiqModel())
            ->setMessage($pidKiqProcessor->getMessage());
    }
}
