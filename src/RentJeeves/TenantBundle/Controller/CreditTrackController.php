<?php
namespace RentJeeves\TenantBundle\Controller;

use Payum\Request\BinaryMaskStatusRequest;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType as PaymentAccountFromType;
use RentJeeves\DataBundle\Model\PaymentAccount;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\DataBundle\Entity\UserSettings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\Serializer\SerializationContext;
use DateTime;

class CreditTrackController extends Controller
{
    /**
     * Render the credit track signup/pay dialog
     *
     * @Template()
     * @return array
     */
    public function payAction()
    {
        $rtMerchantName = $this->container->getParameter('rt_merchant_name');

        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('DataBundle:Group')
            ->findOneByCode($rtMerchantName);
        /** @var Tenant $user */
        $user = $this->getUser();
        $paymentAccounts = $user->getPaymentAccounts();
        $serializer = $this->get('jms_serializer');

        $paymentAccounts = $serializer->serialize(
            $paymentAccounts,
            'json',
            SerializationContext::create()->setGroups(array('paymentAccounts'))
        );
        $group = $serializer->serialize(
            $group,
            'json',
            SerializationContext::create()->setGroups(array('paymentAccounts'))
        );

        return array(
            'paymentGroupJson' => $group,
            'paymentAccountsJson' => $paymentAccounts,
        );
    }

    /**
     * @Route("/credittrack/exec", name="credittrack_pay_exec", options={"expose"=true})
     */
    public function execAction(Request $request)
    {
        /** @var Tenant $user */
        $user = $this->getUser();
        $params = $request->get(PaymentAccountFromType::NAME);
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

        /** @var BinaryMaskStatusRequest $statusRequest */
        $statusRequest = $this->get('payment.pay_credit_track')
            ->executePaymentAccount($paymentAccount);

        if ($statusRequest->isSuccess()) {
            $settings = $user->getSettings();
            $settings->setCreditTrackPaymentAccount($paymentAccount);
            $settings->setCreditTrackEnabledAt(new DateTime('now'));

            $em->persist($settings);
            $em->flush();

            return new JsonResponse(
                array(
                    'success' => true
                )
            );
        } else {
            return new JsonResponse(
                array(
                    PaymentAccountFromType::NAME => array(
                        '_globals' => array($statusRequest->getModel()->getMessages())
                    )
                )
            );
        }
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
