<?php
namespace RentJeeves\TenantBundle\Controller;

use CreditJeeves\DataBundle\Entity\OrderSubmerchant;
use CreditJeeves\DataBundle\Enum\OrderStatus;
use RentJeeves\DataBundle\Entity\Tenant;
use RentJeeves\DataBundle\Enum\PaymentAccountType;
use RentJeeves\CheckoutBundle\Form\Type\PaymentAccountType as PaymentAccountFromType;
use RentJeeves\DataBundle\Model\PaymentAccount;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
        $paymentAccounts = $user->getPaymentAccounts()->filter(function (PaymentAccount $paymentAccount) {
            if (PaymentAccountType::BANK == $paymentAccount->getType()//Temporary #RT-529
                || $paymentAccount->getDeletedAt()
            ) {
                return false;
            }

            return true;
        });
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
        $chargeDay = (new DateTime('now'))->format('j');
        if ($user->getSettings()->isCreditTrack()) {
            $chargeDay = $user->getSettings()->getCreditTrackEnabledAt()->format('j');
        }

        return array(
            'paymentGroupJson' => $group,
            'paymentAccountsJson' => $paymentAccounts,
            'creditTrackEnabled' => $user->getSettings()->isCreditTrack(),
            'chargeDay' => $chargeDay,
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
        $settings = $user->getSettings();
        if ($settings->isCreditTrack()) {
            $settings->setCreditTrackPaymentAccount($paymentAccount);
            $em->persist($settings);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'credittrack.pay.saved');

            return new JsonResponse(
                array(
                    'success' => true,
                    'url' => $this->generateUrl('user_plans'),
                )
            );
        }

        /** @var OrderSubmerchant $order */
        $order = $this->get('payment.pay_credit_track')
            ->executePaymentAccount($paymentAccount);

        if (OrderStatus::COMPLETE === $order->getStatus()) {
            $settings->setCreditTrackPaymentAccount($paymentAccount);
            $settings->setCreditTrackEnabledAt(new DateTime('now'));

            $em->persist($settings);
            $em->flush();

            return new JsonResponse(
                array(
                    'success' => true,
                    'url' => $this->generateUrl('user_report'),
                )
            );
        } else {
            return new JsonResponse(
                array(
                    PaymentAccountFromType::NAME => array(
                        '_globals' => array($order->getErrorMessage())
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
        /** @var Tenant $user */
        $user = $this->getUser();

        return array(
            'creditTrackEnabled' => $user->getSettings()->isCreditTrack()
        );
    }

    /**
     * @Route("/credittrack/cancel", name="credittrack_cancel", options={"expose"=true})
     */
    public function cancelAction()
    {
        /** @var Tenant $user */
        $user = $this->getUser();
        if ($settings = $user->getSettings()) {
            $settings->setCreditTrackPaymentAccount(null);
            $settings->setCreditTrackEnabledAt(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($settings);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user_plans'));
    }
}
