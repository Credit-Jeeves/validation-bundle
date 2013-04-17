<?php

namespace CreditJeeves\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
        return array();
    }
}
