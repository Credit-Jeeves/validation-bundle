<?php

namespace CreditJeeves\PublicBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CreditJeeves\ApplicantBundle\Form\Type\LeadNewType;
use CreditJeeves\DataBundle\Entity\Lead;
use CreditJeeves\DataBundle\Entity\User;
use CreditJeeves\DataBundle\Entity\Group;

class AdminController extends Controller
{
    /**
     * @Route("/back_to_admin", name="public_back_to_admin")
     */
    public function indexAction()
    {
        $adminId = $this->get('session')->get('observe_admin_id');
        if (!$adminId || !($token = $this->get('security.context')->getToken())) {
            throw $this->createNotFoundException('It is not admin');
        }
        $type = $token->getUser()->getType();
        $token->setUser($this->get('doctrine.orm.entity_manager')->getRepository('DataBundle:User')->find($adminId));

        $this->get('session')->set('observe_admin_id', null);

        return $this->redirect($this->get('router')->generate("admin_{$type}_list"));
    }
}
