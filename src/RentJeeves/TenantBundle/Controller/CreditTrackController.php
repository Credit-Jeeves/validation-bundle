<?php
namespace RentJeeves\TenantBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use RentJeeves\CheckoutBundle\Form\Type\PaymentType;
use RentJeeves\DataBundle\Entity\Heartland;
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
        $paymentType = $this->createForm(
            new PaymentType(
                $this->container->getParameter('payment_one_time_until_value'),
                array()
            )
            // array('2014-06-27' => 'Jun') TODO: deal with paidFor
        );

        $creditTrackParams = $this->getCreditTrackParams();

        return array(
            'paymentGroup' => $creditTrackParams['group'],
            'paymentType' => $paymentType->createView(),
            'paymentAccounts' => $this->getUser()->getPaymentAccounts()
        );
    }

    /**
     * @return array
     */
    private function getCreditTrackParams()
    {
      $em = $this->getDoctrine()->getManager();
      $rt_merchant_name = $this->container->getParameter('rt_merchant_name');

      return array(
        'group' => $em->getRepository('DataBundle:Group')->findByCode($rt_merchant_name)[0]
      );
    }

    private function getStatics()
    {
      return array(
        'renttrack_group_code' => 'RentTrack'
      );
    }

    /**
     * @Route("/credittrack/exec", name="credittrack_pay_exec", options={"expose"=true})
     * **Method({"POST"})
     */
    public function execAction(Request $request)
    {
        $request = $this->get('request');
        $params = $request->get('rentjeeves_checkoutbundle_paymenttype');
        $statics = $this->getStatics();

        $em = $this->getDoctrine()->getManager();
        $paymentAccount = $em->getRepository('RjDataBundle:PaymentAccount')
          ->findById($params['paymentAccountId'])[0];

        $em = $this->get('doctrine.orm.default_entity_manager');
        $em->persist($contract);
        $em->persist($paymentEntity);
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
