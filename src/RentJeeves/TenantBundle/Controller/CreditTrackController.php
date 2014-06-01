<?php
namespace RentJeeves\TenantBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\DataBundle\Entity\Heartland;
use RentJeeves\DataBundle\Entity\UserSettings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Payum\Heartland\Soap\Base\RegisterTokenToAdditionalMerchantRequest;
use Payum\Heartland\Model\TokenReregistration;
use Payum\Request\CaptureRequest;

class CreditTrackController extends Controller
{
    /**
     * @Template()
     * @return array
     */
    public function payAction()
    {
        $rt_merchant_name = $this->container->getParameter('rt_merchant_name');

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('DataBundle:Group')
            ->findOneByCode($rt_merchant_name);
        $user = $this->getUser();

        return array(
            'paymentGroup' => $group,
            'paymentAccounts' => $user->getPaymentAccounts(),
        );
    }

    /**
     * @Route("/credittrack/exec", name="credittrack_pay_exec", options={"expose"=true})
     * **Method({"POST"})
     */
    public function execAction(Request $request)
    {
        $user = $this->getUser();
        $request = $this->get('request');
        $params = $request->get('rentjeeves_checkoutbundle_paymentaccounttype');
        $paymentAccountId = $params['id'];

        $em = $this->getDoctrine()->getManager();

        // only allowed to use this user's payment account
        $paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')
            ->findOneBy(array('id' => $paymentAccountId, 'user' => $user));

        if (!$paymentAccount) {
            throw $this->createNotFoundException(
                "PaymentAccount with id '{$paymentAccountId}' not found for user"
            );
        }

        $settings = $user->getSettings();

        if (!$settings) {
            $settings = new UserSettings();
            $settings->setUser($user);

            // TODO: Is this correct? It cannot be null
            $settings->setIsBaseOrderReport(false);
        }

        $settings->setCreditTrackPaymentAccount($paymentAccount);
        $settings->setCreditTrackEnabledAt(new \DateTime('now'));

        $em->persist($settings);
        $em->flush();

        return new JsonResponse(
            array(
                'success' => true
            )
        );
    }

    /**
     * @Template()
     * @return array
     */
    public function promoboxAction()
    {
        return array();
    }

    /**
     * @Template()
     * @return array
     */
    public function pricingAction()
    {
        return array();
    }
}
