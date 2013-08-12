<?php
namespace CreditJeeves\CheckoutBundle\Controller;

use CreditJeeves\CheckoutBundle\Form\Type\AuthorizeNetAimType;
use CreditJeeves\CheckoutBundle\Form\Type\OrderAuthorizeType;
use CreditJeeves\DataBundle\Entity\CheckoutAuthorizeNetAim;
use CreditJeeves\DataBundle\Entity\Order;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use Payum\AuthorizeNet\Aim\Model\PaymentDetails;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\CaptureRequest;
//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\CoreBundle\Controller\ApplicantController as Controller;
use Symfony\Component\Form\FormError;
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
     * @var \Symfony\Component\Form\Form
     */
    protected $form;

    /**
     * @return \Payum\Bundle\PayumBundle\Registry\ContainerAwareRegistry
     */
    protected function getPayum()
    {
        return $this->get('payum');
    }

    /**
     * @Route("/checkout", name="checkout_default")
     * @Route("/tenant/checkout", name="checkout_tenant")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $user = $this->getuser();
        $type = $user->getType();
        $this->order = new Order();
        $this->order->setUser($user);
        $this->form = $this->createForm(new OrderAuthorizeType(), $this->order);

        if ($request->isMethod('POST')) {
            $this->form->bind($request);
            if ($this->form->isValid()) {
                $this->order->setStatus(OrderStatus::NEWONE);
                $authorize = $this->order->getAuthorize();
                $authorize->setOrder($this->order);
                $authorize->setFirstName($this->form->getData()->getUser()->getFirstName());
                $authorize->setLastName($this->form->getData()->getUser()->getLastName());
                $authorize->setAddress($this->form->getData()->getUser()->getStreetAddress1());
                $authorize->setCity($this->form->getData()->getUser()->getCity());
                $authorize->setState($this->form->getData()->getUser()->getState());
                $authorize->setZip($this->form->getData()->getUser()->getZip());
                $authorize->setAmount('9.00');
                $this->order->setAuthorize(); // TODO Fix it
                $this->get('doctrine.orm.default_entity_manager')->persist($this->order);
                $this->get('doctrine.orm.default_entity_manager')->flush();

                if ($this->process($authorize)) {
                    $this->order->setStatus(OrderStatus::COMPLETE);
                    $this->get('doctrine.orm.default_entity_manager')->persist($this->order);
                    $this->get('doctrine.orm.default_entity_manager')->flush();
                    $this->order->setAuthorize($authorize); // TODO Fix it
                    switch ($type) {
                        case 'tenant':
                            return $this->redirect($this->generateUrl('tenant_report'));
                            break;
                        default:
                            $this->get('creditjeeves.mailer')->sendReceipt($this->order);
                            return $this->redirect($this->generateUrl('applicant_report'));
                            break;
                    }
                }
            }
        }
        switch ($type) {
            case 'tenant':
                return $this->render(
                    'CheckoutBundle:Default:rj_index.html.twig',
                    array(
                        'form' => $this->form->createView()
                    )
                );
                break;
            default:
                return array(
                    'form' => $this->form->createView()
                );
            break;
        }
    }

    protected function process(CheckoutAuthorizeNetAim $authorize)
    {
        $payment = $this->getPayum()->getPayment('simple_purchase_authorize_net');
        $captureRequest = new CaptureRequest($authorize);
        $payment->execute($captureRequest);
        $authorize = $captureRequest->getModel();
        $this->get('doctrine.orm.default_entity_manager')->persist($authorize);
        $this->get('doctrine.orm.default_entity_manager')->flush(); // TODO remove and check

        if (\AuthorizeNetAIM_Response::APPROVED != $authorize->getResponseCode()) {
            $code = $authorize->getResponseReasonCode();
            $message = '';
            if (in_array($code, array(6, 37, 7, 8, 27, 127, 290, 78, 44))) {
                $message = "authorize-net-aim-error-message-{$code}";
            }

            $baseMessage = 'authorize-net-aim-error-main-message-' .
                $authorize->getResponseCode() . '-%MESSAGE%-%SUPPORT_EMAIL%';
            $this->form->addError(
                new FormError(
                    $this->get('translator.default')->trans(
                        $baseMessage,
                        array(
                            '%MESSAGE%' => $this->get('translator.default')->trans($message, array(), 'checkout'),
                            '%SUPPORT_EMAIL%' => $this->container->getParameter('support_email')
                        ),
                        'checkout'
                    )
                )
            );
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
        $user = $this->getUser();
        $type = $user->getType();
        switch ($type) {
            case 'tenant':
                $link = 'checkout_tenant';
                break;
            default:
                $link = 'checkout_default';
                break;
        }
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
            'link' => $link,
        );
    }
}
