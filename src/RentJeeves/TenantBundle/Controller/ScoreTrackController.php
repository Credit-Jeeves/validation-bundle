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

class ScoreTrackController extends Controller
{
    /**
     * @Template()
     * @return array
     */
    public function payAction()
    {
        $paymentType = $this->createForm(
            new PaymentType($this->container->getParameter('payment_one_time_until_value'))
        );

        $scoreTrackParams = $this->getScoreTrackParams();

        return array(
            'paymentGroup' => $scoreTrackParams['group'],
            'paymentType' => $paymentType->createView(),
            'paymentAccounts' => $this->getUser()->getPaymentAccounts()
        );
    }

    /**
     * @return array
     */
    private function getScoreTrackParams()
    {
      $em = $this->getDoctrine()->getManager();

      return array(
        'group' => $em->getRepository('DataBundle:Group')->findByCode('RentTrack')[0]
      );
    }

    private function getStatics()
    {
      return array(
        'renttrack_group_code' => 'RentTrack'
      );
    }

    /**
     * @Route("/scoretrack/exec", name="scoretrack_pay_exec", options={"expose"=true})
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
        $this->ensureTokenForScoreTrack($paymentAccount);


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
     * Make sure this PaymentAccount has a scoretrack token. If not, get a
     * token for ScoreTrack using RegisterTokenToAdditionalMerchant
     *
     * @param PaymentAccount $paymentAccount
     */
    private function ensureTokenForScoreTrack($paymentAccount)
    {
        // TODO: How should we determine this?
        // if ($paymentAccount->hasScoreTrackToken()) {
        //     return true;
        // }

        $scoreTrackParams = $this->getScoreTrackParams();
        $token = $paymentAccount->getToken();

        $soapRequest = new RegisterTokenToAdditionalMerchantRequest();
        $reregistration = new TokenReregistration();
        $reregistration->setOldMerchantName($paymentAccount->getGroup()->getMerchantName());
        $reregistration->setNewMerchantName($scoreTrackParams['group']->getMerchantName());
        $reregistration->setToken($token);
        $reregistration->setRequest($soapRequest);
        $payum = $this->get('payum')->getPayment('heartland');
        $captureRequest = new CaptureRequest($reregistration);
        $payum->execute($captureRequest);

        die();

        // BAD, just testing
        $ref = new \ReflectionClass($heartland);
        $property = $ref->getProperty('apis');
        $property->setAccessible(true);
        $api = $property->getValue($heartland)[0];
        $soapClient = $api->getSoapClient();


        $oldMerchantCredentials = $api->getMerchantCredentials($paymentAccount->getGroup()->getMerchantName());
        $newMerchantCredentials = $api->getMerchantCredentials($scoreTrackParams['group']->getMerchantName());
        $soapRequest = new RegisterTokenToAdditionalMerchantRequest();
        $soapRequest->setCredential($oldMerchantCredentials);
        $soapRequest->setRegisterToMerchantCredential($newMerchantCredentials);
        $soapRequest->setToken($token);
        $response = $soapClient->RegisterTokenToAdditionalMerchant($soapRequest);
        var_dump($response);die();

        // TODO: RegisterTokenToAdditionalMerchant
        // RegisterTokenToAdditionalMerchant($token, $scoreTrackParams['group']);
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
