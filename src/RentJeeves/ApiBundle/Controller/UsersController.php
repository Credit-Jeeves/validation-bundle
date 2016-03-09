<?php

namespace RentJeeves\ApiBundle\Controller;

use CreditJeeves\DataBundle\Entity\Holding;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\View\View;
use RentJeeves\ApiBundle\Forms\TenantType;
use RentJeeves\ApiBundle\Response\Exception\ResponseResourceException;
use RentJeeves\DataBundle\Entity\PartnerUser;
use RentJeeves\DataBundle\Entity\ResidentMapping;
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
     *         201="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         409="User exists.",
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
     * @RequestParam(
     *     name="holding_id",
     *     strict=false,
     *     nullable=true,
     *     description="Holding id in RentTrack system."
     * )
     * @RequestParam(
     *     name="resident_id",
     *     strict=false,
     *     nullable=true,
     *     description="Accounting system resident id."
     * )
     *
     *
     * @throws BadRequestHttpException
     * @return \Symfony\Component\Form\Form
     */
    public function createUserAction(Request $request)
    {
        $form = $this->createForm(
            new TenantType(),
            new Tenant(),
            [
                'method' => 'POST',
                'holding_repository' => $this->getDoctrine()->getManager()->getRepository('DataBundle:Holding')
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                /** @var Tenant $tenant */
                $tenant = $form->getData();
                $em = $this->getDoctrine()->getManager();
                /** @var Holding $holding */
                $holding = $form->get('holding_id')->getData();
                $residentId = $form->get('resident_id')->getData();

                if ($em->getRepository('DataBundle:User')->findOneByEmail($tenant->getEmail()) ||
                    ($holding && $residentId && $em->getRepository('RjDataBundle:ResidentMapping')->findOneBy([
                        'holding' => $holding,
                        'residentId' => $residentId
                    ]))
                ) {
                    return View::create(null, 409);
                }

                /** @var PartnerUser $partnerUser */
                $partnerUser = $this->getUser();
                $partner = $partnerUser->getPartner();
                $tenant->setPartner($partner);

                if ($holding && $residentId) {
                    $residentMapping = new ResidentMapping();
                    $residentMapping->setResidentId($residentId);
                    $residentMapping->setHolding($holding);
                    $residentMapping->setTenant($tenant);

                    $tenant->addResidentsMapping($residentMapping);

                    $em->persist($residentMapping);
                }

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
