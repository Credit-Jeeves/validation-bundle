<?php
namespace CreditJeeves\CheckoutBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/checkout", name="checkout_default")
     * @Template()
     */
    public function indexAction()
    {
        $form = $this->createPurchaseForm();

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    protected function createPurchaseForm()
    {
        return $this->createFormBuilder()
                ->add(
                    'amount',
                    null,
                    array(
                        'data' => 1.23,
                        'constraints' => array(new Range(array('max' => 2)))
                    )
                )
                ->add('card_number', null, array('data' => '4007000000027'))
                ->add('card_expiration_date', null, array('data' => '10/16'))

                ->getForm();
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
