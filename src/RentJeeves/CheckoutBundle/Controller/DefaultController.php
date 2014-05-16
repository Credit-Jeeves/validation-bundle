<?php

namespace RentJeeves\CheckoutBundle\Controller;

use Payum\Heartland\Soap\Base\ACHAccountType;
use Payum\Heartland\Soap\Base\ACHDepositType;
use Payum\Heartland\Soap\Base\GetTokenRequest;
use Payum\Heartland\Soap\Base\TokenPaymentMethod;
use Payum\Request\CaptureRequest;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\CheckoutBundle\Form\Type\UserDetailsType;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\DataBundle\Entity\PaymentAccount;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \DateTime;
use Symfony\Component\HttpFoundation\Request;
use RentJeeves\CoreBundle\Controller\Traits\FormErrors;

class DefaultController extends Controller
{
    use FormErrors;

    /**
     * @Route("/checkout/get_token")
     */
    public function indexAction()
    {

        $request = new GetTokenRequest();

        $request->setACHAccountType(ACHAccountType::PERSONAL);
        $request->setACHDepositType(ACHDepositType::CHECKING);
        $request->getAccountHolderData()->setAddress('123 Main Street');
        $request->getAccountHolderData()->setCity('Washington');
        $request->getAccountHolderData()->setEmail('JQAdams@gmail.com');
        $request->getAccountHolderData()->setFirstName('John');
        $request->getAccountHolderData()->setLastName('Adams');
        $request->getAccountHolderData()->setPhone('1112223333');
        $request->getAccountHolderData()->setState('DC');
        $request->getAccountHolderData()->setZip('12321');
        $request->setAccountNumber('5473500000000014');
        $now = new DateTime('+1 year');
        $request->setExpirationMonth($now->format('m'));
        $request->setExpirationYear($now->format('Y'));
        $request->setPaymentMethod(TokenPaymentMethod::ACH);
        $request->setRoutingNumber('062202574'); // TODO find out!

        $paymentDetails = new Heartland();
        $paymentDetails->setMerchantName('Monticeto_Percent');
        $paymentDetails->setRequest($request);

        $this->get('payum')->getPayment('heartland')->execute(new CaptureRequest($paymentDetails));

//        var_dump($paymentDetails->getRequest()->getCredential());

        $this->get('doctrine.orm.entity_manager')->persist($paymentDetails);
        $this->get('doctrine.orm.entity_manager')->flush($paymentDetails);

        return array();
    }


    /**
     * @Route("/checkout/test")
     * @Template()
     */
    public function testAction(Request $request)
    {
        $paymentAccountType = $this->createForm(new PaymentType());

        if ($request->isMethod('POST')) {
            $paymentAccountType->handleRequest($request);
            if ($paymentAccountType->isValid()) {

            }
        }

        ini_set('memory_limit', -1);
        return array(
            'paymentDetailsType' => $paymentAccountType->createView()
        );
    }
    /**
     * @Route("/checkout/test2")
     * @Template("RjCheckoutBundle:Default:test.html.twig")
     */
    public function test2Action(Request $request)
    {
        $paymentAccountType = $this->createForm(new PaymentType($this->getUser()));

        if ($request->isMethod('POST')) {
            $paymentAccountType->handleRequest($request);
            if ($paymentAccountType->isValid()) {

            } else {
                return $this->renderErrors($paymentAccountType);
            }
        }

        ini_set('memory_limit', -1);
        return array(
            'paymentDetailsType' => $paymentAccountType->createView()
        );
    }
}
