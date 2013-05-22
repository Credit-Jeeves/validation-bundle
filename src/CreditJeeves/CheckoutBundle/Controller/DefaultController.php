<?php
namespace CreditJeeves\CheckoutBundle\Controller;

use CreditJeeves\CheckoutBundle\Form\Type\CheckoutAuthorizeNetAimType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use \DateTime;
use Symfony\Component\Validator\Constraints\Range;
use Payum\Registry\AbstractRegistry;

/**
 * @method \CreditJeeves\DataBundle\Entity\Applicant getUser
 */
class DefaultController extends Controller
{
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
                $data = $form->getData();

                $storage = $this->getPayum()->getStorageForClass(
                    'Acme\PaymentBundle\Model\AuthorizeNetPaymentDetails',
                    'authorize_net'
                );

                $paymentDetails = $storage->createModel();
                $paymentDetails->setAmount($data['amount']);
                $paymentDetails->setCardNum($data['card_number']);
                $paymentDetails->setExpDate($data['card_expiration_date']);

                $storage->updateModel($paymentDetails);

                $captureToken = $this->getTokenizedTokenService()->createTokenForCaptureRoute(
                    'authorize_net',
                    $paymentDetails,
                    'acme_payment_details_view'
                );

                return $this->redirect($this->generateUrl('applicant_report'));
            }
        }

        return array(
            'form' => $form->createView()
        );
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
