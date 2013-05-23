<?php

namespace CreditJeeves\PublicBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\LeadNewType;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Group;

class CheckController extends Controller
{
    /**
     * @Route("/new/check/{code}", name="applicant_new_check")
     * @Template()
     *
     * @return array
     */
    public function indexAction($code)
    {
//         $request = $this->get('request');
//         $query = $request->query;
//         $Lead = new Lead();
//         $User = $this->get('core.session.applicant')->getUser();
//         $Group = new Group();
//         if ($request->getMethod() == 'GET') {
//             // Group code
//             if ($query->has('g')) {
//                 $Group->setCode($query->get('g'));
//             }
//             // User details
//             $User = $this->bindUserDetails($User, $query);
//         }
//         $Lead->setUser($User);
//         //$Lead->setGroup($Group);
//         $form = $this->createForm(
//             new LeadNewType(),
//             $Lead,
//             array(
//                 'em' => $this->getDoctrine()->getManager()
//                 )
//         );
//         if ($request->getMethod() == 'POST') {
//             $form->bind($request);
//             if ($form->isValid()) {
//                 $Lead = $form->getData();
//                 if ($this->validateLead($Lead)) {
//                     $User = $Lead->getUser();
//                     $User->setUsername($User->getEmail());
//                     $User->setIsVerified('none');
//                     $User->setType('applicant');

//                     $em = $this->getDoctrine()->getManager();
//                     $em->persist($User);
//                     $em->persist($Lead);
//                     $em->flush();

//                     $this->get('core.session.applicant')->setLeadId($Lead->getId());
//                     $this->get('creditjeeves.mailer')->sendCheckEmail($User);
//                     return $this->redirect($this->generateUrl('applicant_homepage'));

//                 } else {
//                     // FIXME this text must be moved to i18n file
//                     $this->get('session')->getFlashBag()->add(
//                         'notice',
//                         'You are already associated with this dealership. Please contact the dealership at ' .
//                         $Lead->getGroup()->getName() . ' if you wish to change your salesperson.'
//                     );
//                 }
//             }
//         }

        return array(
        );
    }
}
