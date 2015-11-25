<?php
namespace CreditJeeves\UserBundle\Controller;

use CreditJeeves\DataBundle\Entity\MailingAddress as Address;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\UserBundle\Form\Type\LeadReturnedType;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Lead;

class ReturnedController extends Controller
{
    /**
     * @Route("/returned", name="applicant_returned")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $request = $this->get('request');
        /** @var User $User */
        $User = $this->get('core.session.applicant')->getUser();
        if ($User->getAddresses()->count() <= 0) {
            $address = new Address();
            $address->setUser($User);
            $User->addAddress($address);
        }
        $Lead = new Lead();

        $Lead->setUser($User);

        $form = $this->createForm(
            new LeadReturnedType(),
            $Lead,
            array('em' => $this->getDoctrine()->getManager())
        );
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $Lead = $form->getData();
                $Group = $Lead->getGroup();
                //echo $User->getSsn();
                // @TODO would be fixed with right logic
                $Lead->setDealer($this->getLeadDealer($Lead));
                $Lead->setTargetScore($Group->getTargetScore());
                $Lead->setStatus($Lead::STATUS_NEW);
                $Lead->setSource('webpage');

                $em = $this->getDoctrine()->getManager();
                if ($this->validateLead($Lead)) {
                    $em->persist($Lead);
                }
                $User->setHasData(true);
                $em->persist($User);
                $em->flush();
                $this->get('core.session.applicant')->setLeadId($Lead->getId());

                return $this->redirect($this->generateUrl('applicant_homepage'));
            }
        }

        return array('form' => $form->createView());
    }

    private function validateLead($Lead)
    {
        return true;
    }

    private function getLeadDealer($Lead)
    {
        $Group = $Lead->getGroup();

        return $Group->getGroupDealers()->first();
    }
}
