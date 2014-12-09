<?php

namespace RentJeeves\ApiBundle\Controller\Tenant;

use FOS\RestBundle\Controller\FOSRestController as Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RentJeeves\ApiBundle\Forms\PaymentType;
use RentJeeves\DataBundle\Entity\PaymentRepository;
use RentJeeves\DataBundle\Enum\PaymentType as PaymentTypeEnum;
use RentJeeves\ApiBundle\Request\Annotation\AttributeParam;
use RentJeeves\ApiBundle\Request\Annotation\RequestParam;
use RentJeeves\ApiBundle\Response\Payment as ResponseEntity;
use RentJeeves\ApiBundle\Response\ResponseCollection;
use RentJeeves\CheckoutBundle\Controller\Traits\PaymentProcess;
use RentJeeves\DataBundle\Entity\Payment as PaymentEntity;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \RuntimeException;

class PaymentsController extends Controller
{
    use PaymentProcess;

    /**
     * @ApiDoc(
     *     resource=true,
     *     section="Payment",
     *     description="Get all payments for tenant.",
     *     statusCodes={
     *         200="Returned when successful",
     *         204="No content with such parameters",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/payments")
     * @Rest\View(serializerGroups={"Base", "PaymentDetails"})
     *
     * @return ResponseCollection
     */
    public function getPaymentsAction()
    {
        /** @var Tenant $user */
        $user = $this->getUser();

        $payments = $this
            ->getDoctrine()
            ->getRepository('RjDataBundle:Payment')
            ->findByUser($user);

        $response = new ResponseCollection($payments);

        if ($response->count() > 0) {
            return $response;
        }
    }

    /**
     * @param int $id
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Payments",
     *     description="Show payment details.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Payment Account not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Get("/payments/{id}")
     * @Rest\View(serializerGroups={"Base", "PaymentDetails"})
     * @AttributeParam(
     *     name="id",
     *     encoder = "api.default_id_encoder"
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function getPaymentAction($id)
    {
        $payment = $this
            ->getDoctrine()
            ->getRepository('RjDataBundle:Payment')
            ->findOneByIdForUser($id, $this->getUser());

        if ($payment) {
            return $this->get('response_resource.factory')->getResponse($payment);
        }

        throw new NotFoundHttpException('Payment not found');
    }

    /**
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Payments",
     *     description="Create a payment.",
     *     statusCodes={
     *         200="Returned when successful",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Post("/payments")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=201)
     * @RequestParam(
     *     name="contract_url",
     *     encoder="api.default_url_encoder",
     *     description="Resource url for Contract."
     * )
     * @RequestParam(
     *     name="payment_account_url",
     *     encoder="api.default_url_encoder",
     *     description="Resource url for PaymentAccount."
     * )
     * @RequestParam(
     *     name="type",
     *     requirements="recurring|one_time",
     *     description="Payment type should be only 'recurring' or 'one_time'."
     * )
     * @RequestParam(
     *     name="rent",
     *     description="Rent amount. include decimal."
     * )
     * @RequestParam(
     *     name="other",
     *     description="Extra amount to pay. include decimal."
     * )
     * @RequestParam(
     *     name="day",
     *     description="Day of the month to initiate transaction. set to 31st to always pay on last day of month."
     * )
     * @RequestParam(
     *     name="month",
     *     description="For recurring, this is the first month."
     * )
     * @RequestParam(
     *     name="year",
     *     description="For recurring, this is the first year."
     * )
     * @RequestParam(
     *     name="end_year",
     *     description="Used only for recurring, optional.",
     *     default=null,
     *     nullable=true
     * )
     * @RequestParam(
     *     name="end_month",
     *     description="Used only for recurring, optional.",
     *     default=null,
     *     nullable=true
     * )
     * @RequestParam(
     *     name="paid_for",
     *     description="What month is the payment for? '2014-09' is paid for September."
     * )
     *
     * @throws BadRequestHttpException
     * @return ResponseEntity
     */
    public function createPaymentAction(Request $request)
    {
        return $this->processForm($request, new PaymentEntity);
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @ApiDoc(
     *     resource=true,
     *     section="Payment",
     *     description="Update a payment.",
     *     statusCodes={
     *         200="Returned when successful",
     *         404="Payment not found",
     *         400="Error validating data. Please check parameters and retry.",
     *         500="Internal Server Error"
     *     }
     * )
     * @Rest\Put("/payments/{id}")
     * @Rest\View(serializerGroups={"Base", "ApiErrors"}, statusCode=204)
     * @AttributeParam(
     *     name="id",
     *     encoder = "api.default_id_encoder"
     * )
     * @RequestParam(
     *     name="payment_account_url",
     *     encoder="api.default_url_encoder",
     *     description="Resource url for PaymentAccount."
     * )
     * @RequestParam(
     *     name="type",
     *     requirements="recurring|one_time",
     *     description="Payment type should be only 'recurring' or 'one_time'."
     * )
     * @RequestParam(
     *     name="rent",
     *     description="Rent amount. include decimal."
     * )
     * @RequestParam(
     *     name="other",
     *     description="Extra amount to pay. include decimal."
     * )
     * @RequestParam(
     *     name="day",
     *     description="Day of the month to initiate transaction. set to 31st to always pay on last day of month."
     * )
     * @RequestParam(
     *     name="month",
     *     description="For recurring, this is the first month."
     * )
     * @RequestParam(
     *     name="year",
     *     description="For recurring, this is the first year."
     * )
     * @RequestParam(
     *     name="end_year",
     *     description="Used only for recurring, optional.",
     *     default=null,
     *     nullable=true
     * )
     * @RequestParam(
     *     name="end_month",
     *     description="Used only for recurring, optional.",
     *     default=null,
     *     nullable=true
     * )
     * @RequestParam(
     *     name="paid_for",
     *     description="What month is the payment for? '2014-09' is paid for September."
     * )
     *
     * @throws NotFoundHttpException
     * @return ResponseEntity
     */
    public function editPaymentAction($id, Request $request)
    {
        /** @var PaymentRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RjDataBundle:Payment');
        $paymentEntity = $repo->findOneByIdForUser($id, $this->getUser());

        if ($paymentEntity) {
            return $this->processForm($request, $paymentEntity, 'PUT');
        }

        throw new NotFoundHttpException('Payment not found');
    }

    protected function processForm(Request $request, PaymentEntity $entity, $method = 'POST')
    {
        $form = $this->createForm(
            new PaymentType($this->getUser()),
            $entity,
            ['method' => $method]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var PaymentEntity $paymentEntity */
            $paymentEntity = $form->getData();
            $isRecurring = $form->get('type')->getData() ==  PaymentTypeEnum::RECURRING;
            $verifyByPidKiq = false; # TODO: add Pid/Kiq support to API. See https://credit.atlassian.net/browse/RT-853
            try {
                $this->savePayment(
                    $request,
                    $form,
                    $paymentEntity->getContract(),
                    $paymentEntity->getPaymentAccount(),
                    $isRecurring,
                    $verifyByPidKiq
                );

                return $this->get('response_resource.factory')->getResponse($paymentEntity);
            } catch (RuntimeException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $form;
    }
}
