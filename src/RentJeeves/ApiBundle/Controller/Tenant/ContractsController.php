<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use RentJeeves\ApiBundle\Response\Contract as ResponseEntity;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;

class ContractsController extends Controller
{
    /**
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
     *     encoder = "api.default_id_encoder"
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
            return $this->get('response_resource.contract')->setEntity($contract);
        }

        throw new NotFoundHttpException('Contract not found');
    }
}
