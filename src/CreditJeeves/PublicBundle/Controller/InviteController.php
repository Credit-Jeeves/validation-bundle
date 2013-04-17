<?php

namespace CreditJeeves\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditJeeves\ApplicantBundle\Form\Type\UserNewType;

/**
 * 
 * @Route("/invite")
 *
 */
class InviteController extends Controller
{
    /**
     * @Route(
     *     "/{code}",
     *     name="applicant_invite"
     * )
     * @Template()
     * @param string $code
     * @return array
     */
    public function indexAction($code)
    {
        
        
        $request = $this->get('request');
        $User = $this->getDoctrine()->getRepository('DataBundle:User')->findOneBy(array('invite_code' =>  $code));
        if (empty($User)) {
            $this->get('session')->getFlashBag()->add('message_title', 'Title');
            //$this->getContext();//->getI18N()->__();
//             $this->getSession()->setFlash('message_title', 'Title');
//             $this->getSession()->setFlash(
//                     'message_body', 'message'
//             );
//             $this->getContext()->getI18N()->__(
//                     'pidkiq.error.answers-%SUPPORT_EMAIL%',
//                     array('%SUPPORT_EMAIL%' => $this->container->getParameter('support_email'))
                    
            return new RedirectResponse($this->get('router')->generate('public_message_flash'));
            
        }
        $form = $this->createForm(
            new UserNewType(),
            $User
        );
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $User->setInviteCode('');
                $em = $this->getDoctrine()->getManager();
                $em->persist($User);
                $em->flush();
                return new RedirectResponse($this->get('router')->generate('applicant_homepage'));
            }
        }
        return array(
            'code' => $code,
            'form' => $form->createView()
            );
    }
}
