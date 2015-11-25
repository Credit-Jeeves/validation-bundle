<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use RentJeeves\ApiBundle\Forms\ContractType;
use RentJeeves\ApiBundle\Response\Contract as ResponseEntity;
use RentJeeves\ApiBundle\Response\ResponseCollection;
use RentJeeves\ApiBundle\Services\ContractProcessor;
use RentJeeves\DataBundle\Entity\Contract as ContractEntity;
use RentJeeves\DataBundle\Entity\ContractRepository;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;

class ContractsController extends Controller
{
    /**
     * @ApiDoc(
     *     resource=true,
     *     section="Contract",
     *     description="This call allows to get all contracts that belong to the tenant.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/contracts")
     * @Rest\View(serializerGroups={"Base", "ContractShort"})
     *
     * @return ResponseCollection|null
     */
    public function getContractsAction()
    {
        /** @var Tenant $user */
        $user = $this->getUser();

        $response = new ResponseCollection($user->getContracts()->toArray());

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
     *     name="rent",
     *     description="Rent amount. include decimal."
     * )
     * @RequestParam(
     *     name="due_date",
     *     description="Day of the month."
     * )
     * @RequestParam(
     *     name="lease_start",
     *     requirements="\d{4}-\d{2}-\d{2}",
     *     description="Lease start date. Format YYYY-mm-dd."
     * )
     * @RequestParam(
     *     name="lease_end",
     *     strict=false,
     *     requirements="\d{4}-\d{2}-\d{2}",
     *     default=null,
     *     nullable=true,
     *     description="Lease end date. Can be empty if contract is month-to-month. Format YYYY-mm-dd."
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
            [
                'method' => $method,
                'unit_repository' => $this->getDoctrine()->getManager()->getRepository('RjDataBundle:Unit')
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$entity->getId()) {
                /** @var ContractProcessor $processor */
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
     *     name="rent",
     *     description="Rent amount. include decimal."
     * )
     * @RequestParam(
     *     name="due_date",
     *     description="Day of the month."
     * )
     * @RequestParam(
     *     name="lease_start",
     *     requirements="\d{4}-\d{2}-\d{2}",
     *     description="Lease start date. Format YYYY-mm-dd."
     * )
     * @RequestParam(
     *     name="lease_end",
     *     strict=false,
     *     requirements="\d{4}-\d{2}-\d{2}",
     *     default=null,
     *     nullable=true,
     *     description="Lease end date. Can be empty if contract is month-to-month. Format YYYY-mm-dd."
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
