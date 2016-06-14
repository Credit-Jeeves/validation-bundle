<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Forms\TenantDetailsType;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;
use RentJeeves\ApiBundle\Response\Tenant as ResponseEntity;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class TenantController extends Controller
{
    /**
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Details",
     *     description="Show tenant details.",
     *     statusCodes={
     *         200="Returned when successful",
     *         500="Internal Server Error"
     *     },
     *     output={
     *         "class"="RentJeeves\ApiBundle\Response\Tenant",
     *         "groups"={"TenantDetails"}
     *     }
     * )
     * @Rest\Get("/details")
     * @Rest\View(serializerGroups={"TenantDetails"})
     *
     * @return ResponseEntity
     */
    public function detailsAction()
    {
        return $this->get('response_resource.factory')->getResponse($this->getUser());
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Details",
     *     description="Update tenant details",
     *     statusCodes={
     *         204="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     },
     *     responseMap={
     *         204 = {
     *             "class"="RentJeeves\ApiBundle\Response\Tenant",
     *             "groups"={"Empty"}
     *         }
     *     }
     * )
     * @Rest\Put("/details")
     * @Rest\View(serializerGroups={"TenantDetails", "ApiErrors"}, statusCode=204)
     * @RequestParam(
     *     name="first_name",
     *     description="Tenant first name."
     * )
     * @RequestParam(
     *     name="last_name",
     *     description="Tenant last name."
     * )
     * @RequestParam(
     *     name="middle_name",
     *     nullable=true,
     *     description="Tenant middle name."
     * )
     * @RequestParam(
     *     name="email",
     *     nullable=true,
     *     strict=false,
     *     description="Tenant email. Read only."
     * )
     * @RequestParam(
     *     name="phone",
     *     nullable=true,
     *     description="Tenant phone number."
     * )
     * @RequestParam(
     *     name="date_of_birth",
     *     requirements="\d{4}-\d{2}-\d{2}",
     *     description="Tenant date of birth."
     * )
     * @RequestParam(
     *     name="ssn",
     *     requirements="\d{3}-\d{2}-\d{4}",
     *     description="Tenant social security number."
     * )
     *
     * @return ResponseEntity|Form
     */
    public function editAction(Request $request)
    {
        $form = $this->createForm(
            new TenantDetailsType(),
            $this->getUser(),
            [
                'method' => 'PUT',
                'holding_repository' => $this->getDoctrine()->getManager()->getRepository('DataBundle:Holding')
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($this->getUser());
            $em->flush();

            return $this->get('response_resource.factory')->getResponse($this->getUser());
        }

        return $form;
    }
}
