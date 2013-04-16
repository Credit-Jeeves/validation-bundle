<?php

namespace CreditJeeves\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MessageController extends Controller
{
    /**
     * @Route("/message", name="public_message_flash")
     * @Template()
     */
    public function flashAction()
    {
        return array();
    }
}
