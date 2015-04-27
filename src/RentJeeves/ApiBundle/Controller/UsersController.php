<?php

namespace RentJeeves\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use RentJeeves\ApiBundle\Forms\TenantType;
use RentJeeves\ApiBundle\Response\Exception\ResponseResourceException;
use RentJeeves\DataBundle\Entity\PartnerUser;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use RuntimeException;

class UsersController extends Controller
{
    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Users",
     *     description="This call allows the application to get details information about user.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="User not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/users/{id}")
     * @Rest\View(serializerGroups={"Base", "UserDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     *
     * @throws ResponseResourceException
     */
    public function getUserAction($id)
    {
        throw new ResponseResourceException('GetUser action is not implemented yet');
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Users",
     *     description="Create a new tenant user",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Post("/users")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=201)
     * @RequestParam(
     *     name="type",
     *     requirements="tenant",
     *     description="Only user type 'tenant' is supported."
     * )
     * @RequestParam(
     *     name="first_name",
     *     description="User firstname."
     * )
     * @RequestParam(
     *     name="last_name",
     *     description="User lastname."
     * )
     * @RequestParam(
     *     name="email",
     *     description="User email."
     * )
     * @RequestParam(
     *     name="password",
     *     description="User password."
     * )
     *
     * @throws BadRequestHttpException
     * @return \Symfony\Component\Form\Form
     */
    public function createUserAction(Request $request)
    {
        $form = $this->createForm(
            new TenantType($this->getUser()),
            new Tenant(),
            ['method' => 'POST']
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $tenant = $form->getData();
                /** @var PartnerUser $partnerUser */
                $partnerUser = $this->getUser();
                $partner = $partnerUser->getPartner();
                $tenant->setPartner($partner);

                $em = $this->getDoctrine()->getManager();
                $em->persist($tenant);
                $em->flush();

                return $this->get('response_resource.factory')->getResponse($tenant);
            } catch (RuntimeException $e) {
                $form->addError(new FormError($e->getMessage().$e->getTraceAsString()));
            }
        }

        return $form;
    }
}
