<?php
namespace CreditJeeves\CheckoutBundle\Controller;

use CreditJeeves\CheckoutBundle\Form\Type\OrderAuthorizeType;
use CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Payum\AuthorizeNet\Aim\Model\PaymentDetails;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Range;
use \DateTime;

/**
 * @method \CreditJeeves\DataBundle\Entity\Applicant getUser
 */
class DefaultController extends Controller
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $message = '';

    /**
     * @return \Payum\Bundle\PayumBundle\Context\ContextRegistry
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }

    /**
     * @Route("/checkout", name="checkout_default")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $this->order = new Order();
        $this->order->setUser($this->getUser());
        $form = $this->createForm(new OrderAuthorizeType(), $this->order);

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                die('isValid');
                $this->order->setStatus(OrderStatus::NEWONE);
                $this->get('doctrine.orm.default_entity_manager')->persist($this->order);
                $this->get('doctrine.orm.default_entity_manager')->flush();

                if ($this->process($form->getData())) {
                    $this->order->setStatus(OrderStatus::COMPLETE);
                    $this->get('doctrine.orm.default_entity_manager')->persist($this->order);
                    $this->get('doctrine.orm.default_entity_manager')->flush();
                    return $this->redirect($this->generateUrl('applicant_report'));
                }
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    protected function process($data)
    {
        $paymentDetails = new PaymentDetails();
        $paymentDetails->setFirstName($data['first_name']);
        $paymentDetails->setLastName($data['last_name']);
        $paymentDetails->setAddress($data['address1'] . ' ' . $data['address2']);
        $paymentDetails->setCity($data['city']);
        $paymentDetails->setState($data['state']);
        $paymentDetails->setZip($data['zip']);
        $paymentDetails->setCardNum($data['card_number']);
        $paymentDetails->setCardCode($data['card_csc']);
        $paymentDetails->setExpDate(
            (2==strlen($data['card_expiration_date_month'])?
                $data['card_expiration_date_month']:
                '0' . $data['card_expiration_date_month']) .
            $data['card_expiration_date_year']
        );
        $paymentDetails->setAmount('9.00');
//        $model->setCurrency('USD');

        $context = $this->getPayum()->getContext('simple_purchase_authorize_net');

        $captureRequest = new CaptureRequest($paymentDetails);
        $context->getPayment()->execute($captureRequest);

        $authorize = new CheckoutAuthorizeNetAim();
        $authorize->setOrder($this->order);
        $authorize->setCode($paymentDetails->getResponseCode());
        $authorize->setSubcode($paymentDetails->getResponseSubcode());
        $authorize->setReasonCode($paymentDetails->getResponseReasonCode());
        $authorize->setReasonText($paymentDetails->getResponseReasonText());
        $authorize->setAuthorizationCode($paymentDetails->getAuthorizationCode());
        $authorize->setAvs($paymentDetails->getAvsResponse());
        $authorize->setTransactionId($paymentDetails->getTransactionId());
        $authorize->setInvoiceNumber($paymentDetails->getInvoiceNumber());
        $authorize->setDescription($paymentDetails->getDescription());
        $authorize->setMethod($paymentDetails->getMethod());
        $authorize->setTransactionType($paymentDetails->getTransactionType());
        $authorize->setMd5Hash($paymentDetails->getMd5Hash());
        $authorize->setPurchaseOrderNumber($paymentDetails->getPurchaseOrderNumber());
        $authorize->setCardCode($paymentDetails->getCardCodeResponse());
        $authorize->setCardholderAuthenticationValue($paymentDetails->getCardholderAuthenticationValue());
        $authorize->setSplitTenderId($paymentDetails->getSplitTenderId());


        $this->get('doctrine.orm.default_entity_manager')->persist($authorize);
        $this->get('doctrine.orm.default_entity_manager')->flush(); // TODO remove and check

        if (\AuthorizeNetAIM_Response::APPROVED != $paymentDetails->getResponseCode()) {
            $code = $paymentDetails->getResponseReasonCode();
            $message = '';
            if (in_array($code, array(6, 37, 7, 8, 27, 127, 290, 78, 44))) {
                $message = "authorize-net-aim-error-message-{$code}";
            }

            $this->message = 'authorize-net-aim-error-main-message-' .
                $paymentDetails->getResponseCode() . '-%MESSAGE%-%SUPPORT_EMAIL%';
//            $this->form->getErrorSchema()->addError(
//                new sfValidatorError(
//                    $this->form->getValidatorSchema(),
//                    $this->getContext()->getI18N()->__(
//                        $this->message,
//                        array(
//                            '%MESSAGE%' => $this->getContext()->getI18N()->__($message, array(), 'checkout'),
//                            '%SUPPORT_EMAIL%' => sfConfig::get('app_support_email'),
//                        ),
//                        'checkout'
//                    )
//                ),
//                $this->message
//            );
            return false;
        }

        return true;
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like BlogBundle:Post:index)
     * @param array  $path       An array of path parameters
     * @param array  $query      An array of query parameters
     *
     * @return \Symfony\Component\HttpFoundation\Response A Response instance
     */
    public function handle($controller, array $path = array(), array $query = array())
    {
        /** @var $httpKernel \Symfony\Bundle\FrameworkBundle\HttpKernel */
        $httpKernel = $this->container->get('http_kernel');

        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request')->duplicate($query, null, $path);

        return $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST, $catch = false);
    }

    /**
     * @Template()
     *
     * @return array
     */
    public function boxAction()
    {
        $boxMessage = 'box-message';
        $boxNote = 'box-note';

        /** @var \CreditJeeves\DataBundle\Entity\Report $report */
        $report = $this->getUser()
            ->getReportsD2c()
            ->last();

        if (!empty($report)) {
            $now = new DateTime();
            if ($now->modify('-1 month') > $report->getCreatedAt()) {
                $boxMessage = 'box-message-expired';
                $boxNote = 'box-note-expired';
            } else {
                return $this->render('CoreBundle::empty.html.twig');
            }
        }

        return array(
            'boxMessage' => $boxMessage,
            'boxNote' => $boxNote,
        );
    }
}
