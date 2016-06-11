<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use CreditJeeves\DataBundle\Entity\MailingAddress as AddressEntity;
use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Forms\UserAddressType;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;
use RentJeeves\ApiBundle\Response\MailingAddress as ResponseEntity;
use RentJeeves\ApiBundle\Response\ResponseCollection;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AddressesController extends Controller
{
    /**
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Address",
     *     description="This call allows to get all addresses that belong to the tenant.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         500="Internal Server Error"
     *     },
     *     output={
     *         "class"="RentJeeves\ApiBundle\Response\MailingAddress",
     *         "groups"={"Base", "AddressDetails"},
     *         "collection" = true
     *     }
     * )
     * @Rest\Get("/addresses")
     * @Rest\View(serializerGroups={"Base", "AddressDetails"})
     *
     * @return ResponseCollection|null
     */
    public function getAddressesAction()
    {
        /** @var Tenant $user */
        $user = $this->getUser();

        $response = new ResponseCollection($user->getAddresses()->toArray());

        if ($response->count() > 0) {
            return $response;
        }

        return null;
    }

    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Address",
     *     description="This call allows the application to get detailed information about address by id.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Address not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     },
     *     output={
     *         "class"="RentJeeves\ApiBundle\Response\MailingAddress",
     *         "groups"={"Base", "AddressDetails"}
     *     }
     * )
     * @Rest\Get("/addresses/{id}")
     * @Rest\View(serializerGroups={"Base", "AddressDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function getAddressAction($id)
    {
        $address = $this
            ->getDoctrine()
            ->getRepository('DataBundle:MailingAddress')
            ->findOneBy(['user' => $this->getUser(), 'id' => $id]);

        if ($address) {
            return $this->get('response_resource.factory')->getResponse($address);
        }

        throw new NotFoundHttpException('Address not found');
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Address",
     *     description="Create new address.",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     },
     *     responseMap={
     *         201 = {
     *             "class"="RentJeeves\ApiBundle\Response\MailingAddress",
     *             "groups"={"Base"}
     *         }
     *     }
     * )
     * @Rest\Post("/addresses")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=201)
     * @RequestParam(
     *     name="street",
     *     description="Street name with number."
     * )
     * @RequestParam(
     *     name="unit",
     *     nullable=true,
     *     description="Unit name or null if property is standalone."
     * )
     * @RequestParam(
     *     name="city",
     *     description="City name."
     * )
     * @RequestParam(
     *     name="state",
     *     description="State code."
     * )
     * @RequestParam(
     *     name="zip",
     *     description="Zip code."
     * )
     * @RequestParam(
     *     name="is_current",
     *     nullable=true,
     *     default=false,
     *     description="Set address like default, optional, default value is false."
     * )
     *
     * @return ResponseEntity|Form
     */
    public function createAddressAction(Request $request)
    {
        $address = new AddressEntity();
        $address->setUser($this->getUser());

        return $this->processForm($request, $address);
    }

    /**
     * @param Request $request
     * @param AddressEntity $entity
     * @param string $method
     * @return Form|ResponseEntity
     */
    protected function processForm(Request $request, AddressEntity $entity, $method = 'POST')
    {
        $form = $this->createForm(
            new UserAddressType(),
            $entity,
            ['method' => $method]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $address = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($address);
            $em->flush();

            return $this->get('response_resource.factory')->getResponse($address);
        }

        return $form;
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Address",
     *     description="Update an address.",
     *     statusCodes={
     *         204="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         404="Address not found",
     *         500="Internal Server Error"
     *     },
     *     responseMap={
     *         204 = {
     *             "class"=ResponseEntity::class,
     *             "groups"={"Empty"}
     *         }
     *     }
     * )
     * @Rest\Put("/addresses/{id}")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=204)
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     * @RequestParam(
     *     name="street",
     *     description="Street name with number."
     * )
     * @RequestParam(
     *     name="unit",
     *     nullable=true,
     *     description="Unit name or null if property is standalone."
     * )
     * @RequestParam(
     *     name="city",
     *     description="City name."
     * )
     * @RequestParam(
     *     name="state",
     *     description="State code."
     * )
     * @RequestParam(
     *     name="zip",
     *     description="Zip code."
     * )
     * @RequestParam(
     *     name="is_current",
     *     nullable=true,
     *     default=false,
     *     description="Set address like default."
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity|Form
     */
    public function editAddressAction($id, Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('DataBundle:MailingAddress');
        $address = $repo->findOneBy(['user' => $this->getUser(), 'id' => $id]);

        if ($address) {
            return $this->processForm($request, $address, 'PUT');
        }

        throw new NotFoundHttpException('Address not found');
    }
}
