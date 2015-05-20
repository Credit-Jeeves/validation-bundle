<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use RentJeeves\ApiBundle\Forms\ContractType;
use RentJeeves\ApiBundle\Response\Contract as ResponseEntity;
use RentJeeves\ApiBundle\Services\ContractProcessor;
use RentJeeves\DataBundle\Entity\Contract as ContractEntity;
use RentJeeves\DataBundle\Entity\ContractRepository;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;

class ContractsController extends Controller
{
    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Contract",
     *     description="Get details for a specific contract.",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         404="Contract not found",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/contracts/{id}")
     * @Rest\View(serializerGroups={"Base", "ContractDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function getContractAction($id)
    {
        $contract = $this
            ->getDoctrine()
            ->getRepository('RjDataBundle:Contract')
            ->findOneBy(['tenant' => $this->getUser(), 'id' => $id]);

        if ($contract) {
            return $this->get('response_resource.factory')->getResponse($contract);
        }

        throw new NotFoundHttpException('Contract not found');
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Contract",
     *     description="Create new contract.",
     *     statusCodes={
     *         201="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Post("/contracts")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=201)
     * @RequestParam(
     *     name="unit_url",
     *     strict=false,
     *     encoder="api.default_url_encoder",
     *     description="Resource url for Unit."
     * )
     * @RequestParam(
     *     name="new_unit",
     *     array=true,
     *     strict=false,
     *     description="Details info for create new unit with invitation landlord and creating contract."
     * )
     * @RequestParam(
     *     name="experian_reporting",
     *     default="disabled",
     *     requirements="enabled|disabled",
     *     description="Option for enable reporting to Experian."
     * )
     *
     * @return ResponseEntity|Form
     */
    public function createContractAction(Request $request)
    {
        return $this->processForm($request, new ContractEntity());
    }

    /**
     * @param  Request             $request
     * @param  ContractEntity      $entity
     * @param  string              $method
     * @return Form|ResponseEntity
     * @throws \Exception
     */
    protected function processForm(Request $request, ContractEntity $entity, $method = 'POST')
    {
        $form = $this->createForm(
            new ContractType(),
            $entity,
            ['method' => $method]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$entity->getId()) {
                /** @var $processor ContractProcessor */
                $processor = $this->get('api.contract.processor');

                $contract = $processor->process($form, $this->getUser());
            } else {
                $contract = $form->getData();

                $em = $this->getDoctrine()->getManager();
                $em->persist($contract);
                $em->flush();
            }

            return $this->get('response_resource.factory')->getResponse($contract);
        }

        return $form;
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Contract",
     *     description="Update a contract.",
     *     statusCodes={
     *         204="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         404="Contract not found",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Put("/contracts/{id}")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=204)
     * @AttributeParam(
     *     name="id",
     *     encoder="api.default_id_encoder"
     * )
     * This is needed for correct parsing url and get id
     * @RequestParam(
     *     name="unit_url",
     *     strict=false,
     *     encoder="api.default_url_encoder",
     *     description="Resource url for Unit."
     * )
     * @RequestParam(
     *     name="experian_reporting",
     *     requirements="enabled|disabled",
     *     description="Option for enable reporting to Experian."
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity|Form
     */
    public function editContractAction($id, Request $request)
    {
        /** @var ContractRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Contract');
        $contract = $repo->findOneBy(['tenant' => $this->getUser(), 'id' => $id]);

        if ($contract) {
            return $this->processForm($request, $contract, 'PUT');
        }

        throw new NotFoundHttpException('Contract not found');
    }
}
