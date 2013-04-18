<?php

namespace CreditJeeves\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @Route("/message")
 *
 */
class MessageController extends Controller
{
    /**
     * @Route("/", name="public_message_flash")
     * @Template()
     */
    public function flashAction()
    {
        $sTitle = $this->get('session')->getFlashBag()->get('message_title');
        $sText = $this->get('session')->getFlashBag()->get('message_text');
        if (!$sText & !$sTitle) {
            return new Response('', '404');
        }
        return array();
    }
}
