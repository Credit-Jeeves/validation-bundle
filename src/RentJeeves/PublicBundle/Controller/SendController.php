<?php
namespace RentJeeves\PublicBundle\Controller;

use RentJeeves\DataBundle\Entity\Tenant;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SendController extends Controller
{
    /**
     * @Route("/new/send/{userId}", name="user_new_send")
     * @Template()
     *
     * @param int $userId
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
            $this->get('project.mailer')->sendRjCheckEmail($user);
        }

        if ($user instanceof Tenant) {
            $inviteLandlord = $user->getInvite();
            $landlordLetter = (empty($inviteLandlord)) ? false : true;
        }

        return [
            'userId'         => $userId,
            'active'         => $active,
            'landlordLetter' => $landlordLetter,
        ];
    }
}
