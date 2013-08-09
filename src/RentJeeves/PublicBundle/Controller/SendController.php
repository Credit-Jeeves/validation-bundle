<?php
namespace RentJeeves\PublicBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CreditJeeves\DataBundle\Enum\UserType;

class SendController extends Controller
{
    /**
     * @Route("/new/send/{userId}", name="user_new_send")
     * @Template()
     *
     * @return array
     */
    public function indexAction($userId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('DataBundle:User')->find($userId);
        $landlordLetter = false;

        if (empty($user)) {
            return $this->redirect($this->generateUrl("iframe"));
        }

        $request = $this->get('request');
        $active = (is_null($user->getInviteCode())) ? true : false;

        if ($request->getMethod() == 'POST' && $user->getInviteCode()) {
            $this->get('creditjeeves.mailer')->sendRjCheckEmail($user);
        }

        if ($user->getType() != UserType::LANDLORD) {
            $inviteLandlord = $user->getInvite();
            $landlordLetter = (empty($inviteLandlord)) ? false : true;
        }
        
        return array(
            'userId'         => $userId,
            'active'         => $active,
            'landlordLetter' => $landlordLetter,
        );
    }
}
