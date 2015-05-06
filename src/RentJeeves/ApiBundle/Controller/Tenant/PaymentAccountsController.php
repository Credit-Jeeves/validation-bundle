<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Forms\PaymentAccountType;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;
use RentJeeves\ApiBundle\Response\PaymentAccount as ResponseEntity;
use RentJeeves\ApiBundle\Response\ResponseCollection;
use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\DataBundle\Entity\Contract;
use RentJeeves\DataBundle\Entity\PaymentAccount as PaymentAccountEntity;
use RentJeeves\DataBundle\Entity\PaymentAccountRepository;
use RentJeeves\DataBundle\Entity\Tenant;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \RuntimeException;

class PaymentAccountsController extends Controller
{
    use PaymentProcess;

    /**
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Payment Account",
     *     description="This call allows the application to get all accounts belongs to the tenant.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/payment_accounts")
     * @Rest\View(serializerGroups={"Base", "PaymentAccountDetails"})
     *
     * @return ResponseCollection
     */
    public function getPaymentAccountsAction()
    {
        /** @var Tenant $user */
        $user = $this->getUser();

        $response = new ResponseCollection($user->getPaymentAccounts()->toArray());

        if ($response->count() > 0) {
            return $response;
        }
    }

    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Payment Account",
     *     description="This call allows the application to get details information about payment account by id.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Payment Account not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/payment_accounts/{id}")
     * @Rest\View(serializerGroups={"Base", "PaymentAccountDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder = "api.default_id_encoder"
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function getPaymentAccountAction($id)
    {
        $paymentAccount = $this
            ->getDoctrine()
            ->getRepository('RjDataBundle:PaymentAccount')
            ->findOneBy(['user' => $this->getUser(), 'id' => $id]);

        if ($paymentAccount) {
            return $this->get('response_resource.factory')->getResponse($paymentAccount);
        }

        throw new NotFoundHttpException('Payment Account not found');
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Payment Account",
     *     description="Create a payment account.",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Post("/payment_accounts")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=201)
     * @RequestParam(
     *     name="contract_url",
     *     encoder="api.default_url_encoder",
     *     description="Resource url for Contract."
     * )
     * @RequestParam(
     *     name="type",
     *     requirements="bank|card",
     *     description="Payment account type should be only 'bank' or 'card'."
     * )
     * @RequestParam(
     *     name="nickname",
     *     description="Payment account nickname."
     * )
     * @RequestParam(
     *     name="name",
     *     description="Name on credit card or account holder."
     * )
     * @RequestParam(
     *     name="bank",
     *     array=true,
     *     strict=false,
     *     description="Required if type is bank."
     * )
     * @RequestParam(
     *     name="card",
     *     array=true,
     *     strict=false,
     *     description="Required if type is card."
     * )
     *
     * @throws BadRequestHttpException
     * @return ResponseEntity
     */
    public function createPaymentAccountAction(Request $request)
    {
        return $this->processForm($request, new PaymentAccountEntity());
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Tenant Payment Account",
     *     description="Update a payment account.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Payment Account not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Put("/payment_accounts/{id}")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=204)
     * @AttributeParam(
     *     name="id",
     *     encoder = "api.default_id_encoder"
     * )
     * @RequestParam(
     *     name="contract_url",
     *     encoder="api.default_url_encoder",
     *     description="Resource url for Contract."
     * )
     * @RequestParam(
     *     name="type",
     *     requirements="bank|card",
     *     description="Payment account type should be only 'bank' or 'card'."
     * )
     * @RequestParam(
     *     name="nickname",
     *     description="Payment account nickname."
     * )
     * @RequestParam(
     *     name="name",
     *     description="Name on credit card or account holder."
     * )
     * @RequestParam(
     *     name="bank",
     *     array=true,
     *     strict=false,
     *     description="Required if type is bank."
     * )
     * @RequestParam(
     *     name="card",
     *     array=true,
     *     strict=false,
     *     description="Required if type is card."
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function editPaymentAccountAction($id, Request $request)
    {
        /** @var PaymentAccountRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:PaymentAccount');
        $paymentAccountEntity = $repo->findOneBy(['user' => $this->getUser(), 'id' => $id]);

        if ($paymentAccountEntity) {
            return $this->processForm($request, $paymentAccountEntity, 'PUT');
        }

        throw new NotFoundHttpException('Payment Account not found');
    }

    protected function processForm(Request $request, PaymentAccountEntity $entity, $method = 'POST')
    {
        $form = $this->createForm(
            new PaymentAccountType($this->getUser()),
            $entity,
            ['method' => $method]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $paymentAccountEntity = $form->getData();
            /** @var Contract $contract */
            $contract = $form->get('contract_url')->getData();
            try {
                $this->savePaymentAccount($form, $contract);

                return $this->get('response_resource.factory')->getResponse($paymentAccountEntity);
            } catch (RuntimeException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $form;
    }
}
