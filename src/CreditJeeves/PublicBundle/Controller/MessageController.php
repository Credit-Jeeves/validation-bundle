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
        $title = $this->get('session')->getFlashBag()->get('message_title');
        $text = $this->get('session')->getFlashBag()->get('message_body');
        if (!$text & !$title) {
            throw $this->createNotFoundException('Flashes message not found');
        }
        return array(
            'title' => $title,
            'text' => $text,
        );
    }
}
