<?php
namespace CreditJeeves\CheckoutBundle\Controller;

use CreditJeeves\CheckoutBundle\Form\Type\CheckoutAuthorizeNetAimType;
use CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim;
use CreditJeeves\DataBundle\Entity\Order;
use Payum\AuthorizeNet\Aim\PaymentInstruction;
use Payum\Request\CaptureRequest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \DateTime;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @method \CreditJeeves\DataBundle\Entity\Applicant getUser
 */
class DefaultController extends Controller
{
    /**
     * @var Order
     */
    protected $order;

    protected $message = '';

    /**
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

        $form = $this->createForm(new CheckoutAuthorizeNetAimType($this->getUser()));

        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {

                $this->order = new Order();

                if ($this->process($form->getData())) {
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
        $model = new CheckoutAuthorizeNetAim();
        $model->setOrder($this->order);
        $model->setFirstName($data['first_name']);
        $model->setLastName($data['last_name']);
        $model->setAddress($data['address1'] . ' ' . $data['address2']);
        $model->setCity($data['city']);
        $model->setState($data['state']);
        $model->setZip($data['zip']);
        $model->setCardNum($data['card_number']);
        $model->setCardCode($data['card_csc']);
        $model->setExpDate(
            (2==strlen($data['card_expiration_date_month'])?
                $data['card_expiration_date_month']:
                '0' . $data['card_expiration_date_month']) .
            $data['card_expiration_date_year']
        );
        $model->setAmount('9.00');
//        $model->setCurrency('USD');

        $context = $this->getPayum()->getContext('simple_purchase_authorize_net');

        if ($interactiveRequest = $context->getPayment()->execute(new CaptureRequest($model))) {
//            $context->getStorage()->updateModel($model);

            return $this->handle($context->getCaptureInteractiveController(), array(
                    'context' => $context,
                    'interactiveRequest' => $interactiveRequest
                ));
        }

        $statusRequest = $context->createStatusRequest($model);
        if ($interactiveRequest = $context->getPayment()->execute($statusRequest)) {
            throw new LogicException('Unsupported interactive request.', null, $interactiveRequest);
        }

        $response = $this->handle($context->getCaptureFinishedController(), array(
                'context' => $context,
                'statusRequest' => $statusRequest
            ));

        $this->get('octrine.orm.default_entity_manager')->persist($model);
        $this->get('octrine.orm.default_entity_manager')->flush();

        var_dump($response);die('OK');

        if (\AuthorizeNetAIM_Response::APPROVED != $model->getResponseCode()) {
            $code = $model->getResponseReasonCode();
            $message = '';
            if (in_array($code, array(6, 37, 7, 8, 27, 127, 290, 78, 44))) {
                $message = "authorize-net-aim-error-message-{$code}";
            }

            $this->message = 'authorize-net-aim-error-main-message-' .
                $model->getResponseCode() . '-%MESSAGE%-%SUPPORT_EMAIL%';
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
